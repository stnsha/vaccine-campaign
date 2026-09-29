<?php
require_once('vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

require_once('../lock_adv.php');
// Release the session lock right after the login check. A long import would otherwise
// block progress polling and every other page opened with the same session.
session_write_close();

// Progress polling endpoint: returns the snapshot written by a running import.
if (isset($_GET['progress'])) {
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    $poll_token = preg_replace('/[^a-f0-9]/', '', (string) $_GET['progress']);
    $poll_file  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vc_import_' . $poll_token . '.json';
    $snapshot   = ($poll_token !== '' && is_file($poll_file)) ? @file_get_contents($poll_file) : false;
    echo $snapshot !== false && $snapshot !== '' ? $snapshot : '{"pct":0,"text":"Waiting for server..."}';
    exit;
}

$connect = 1;
include('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');

if (isset($_POST['submit'])) {

    $query_outlet  = "SELECT `id`, `code` FROM `outlet` WHERE recycle = 0";
    $result_outlet = mysqli_query($conn, $query_outlet);
    $outlet_arr    = array();
    if ($result_outlet) {
        while ($row_outlet = $result_outlet->fetch_assoc()) {
            $outlet_arr[stripslashes($row_outlet['code'] ?? '')] = stripslashes($row_outlet['id'] ?? '');
        }
    }

    $fileName = $_FILES['userfile']['name'];
    $tmpName  = $_FILES['userfile']['tmp_name'];
    $ext      = strtolower(substr(strrchr($fileName, '.'), 1));

    if ($ext != 'xlsx') {
?>
<style type="text/css">
.idx-panel{background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:18px 22px;margin:10px 0;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;text-align:left !important;}
a.upd-back,.upd-back{display:inline-flex !important;align-items:center !important;background:#e9ecef !important;color:#111 !important;border:1px solid #d0d7de !important;border-radius:8px !important;height:32px !important;padding:5px 18px !important;font-weight:bold !important;text-decoration:none !important;font-family:Arial,Helvetica,sans-serif !important;font-size:13px !important;box-sizing:border-box !important;line-height:1 !important;}
</style>
<div class="idx-panel">
    <p style="color:#c53030 !important;font-size:13px !important;">Unsupported file type. Only .xlsx files are accepted.</p>
    <a href="vaccine_campaign_import.php" class="upd-back">Go Back</a>
</div>
<?php
        $connect = 0;
        include('../common/index_adv.php');
        exit;
    }

    ini_set('memory_limit', '2000M');
    ini_set('max_execution_time', 30000);

    // Stream progress updates to the browser while the import runs.
    if (function_exists('apache_setenv')) {
        @apache_setenv('no-gzip', '1');
    }
    @ini_set('zlib.output_compression', '0');
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    ob_implicit_flush(true);
?>
<style type="text/css">
.imp-progress{background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:18px 22px;margin:10px 0;font-family:Arial,Helvetica,sans-serif !important;text-align:left !important;}
.imp-progress-label{font-size:13px !important;color:#111 !important;margin-bottom:8px !important;display:flex !important;justify-content:space-between !important;}
.imp-progress-track{background:#e9ecef;border-radius:8px;height:18px;overflow:hidden;}
.imp-progress-fill{background:#005B96;height:100%;width:0;transition:width .2s ease;}
</style>
<div class="imp-progress" id="impProgress">
    <div class="imp-progress-label"><span id="impProgressText">Loading file...</span><span id="impProgressPct">0%</span></div>
    <div class="imp-progress-track"><div class="imp-progress-fill" id="impProgressFill"></div></div>
</div>
<script type="text/javascript">
function impProgress(pct, text) {
    document.getElementById('impProgressFill').style.width = pct + '%';
    document.getElementById('impProgressPct').textContent = pct + '%';
    document.getElementById('impProgressText').textContent = text;
}
function impProgressDone() {
    document.getElementById('impProgress').style.display = 'none';
}
</script>
<?php
    // Padding pushes the initial chunk past browser render buffers.
    echo '<!--' . str_repeat(' ', 4096) . '-->';
    flush();

    // Progress is also written to a temp file so the upload page can poll it. Streaming
    // alone is not reliable when the server or network holds back the response.
    $imp_token     = preg_replace('/[^a-f0-9]/', '', (string) ($_POST['imp_token'] ?? ''));
    $progress_file = $imp_token !== '' ? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vc_import_' . $imp_token . '.json' : null;

    $last_pct = -1;
    $emit_progress = function (int $pct, string $text) use (&$last_pct, $progress_file): void {
        if ($pct === $last_pct) {
            return;
        }
        $last_pct = $pct;
        if ($progress_file !== null) {
            @file_put_contents($progress_file, json_encode(['pct' => $pct, 'text' => $text]), LOCK_EX);
        }
        echo '<script type="text/javascript">impProgress(' . $pct . ', ' . json_encode($text) . ');</script>' . "\n";
        flush();
    };

    $emit_progress(2, 'Loading file...');

    try {
        $inputFileType = IOFactory::identify($tmpName);
        $objReader     = IOFactory::createReader($inputFileType);
        $objPHPExcel   = $objReader->load($tmpName);
    } catch (Exception $e) {
        die('Error loading file "' . pathinfo($fileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    $sheet         = $objPHPExcel->getSheet(0);
    $highestRow    = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    $errors  = array();
    $inserts = array();
    $seen    = array();
    $updates   = [];
    $unchanged = 0;
    $today   = date('Y-m-d');

    // Validation covers 5-60%, database writes cover 60-100%.
    $total_rows = max(1, $highestRow - 2);
    $emit_progress(5, 'Validating rows...');

    for ($row = 3; $row <= $highestRow; $row++) {
        $done = $row - 2;
        $emit_progress(5 + (int) floor($done / $total_rows * 55), "Validating row $done of $total_rows...");

        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

        $date = trim((string) $rowData[0][0]);
        $code = trim((string) $rowData[0][1]);
        $ack_raw     = trim((string) ($rowData[0][2] ?? ''));
        $carepro_raw = trim((string) ($rowData[0][3] ?? ''));
        $is_ack      = (strcasecmp($ack_raw, 'yes') === 0);
        $is_carepro  = (strcasecmp($carepro_raw, 'yes') === 0);

        if (empty($date) && empty($code)) {
            continue;
        }
        if (empty($date)) {
            $errors[] = "Row $row: Missing date.";
            continue;
        }
        if (empty($code)) {
            $errors[] = "Row $row: Missing outlet code.";
            continue;
        }
        if (!isset($outlet_arr[$code])) {
            $errors[] = "Row $row: Outlet code '$code' not found in system.";
            continue;
        }

        if (is_numeric($date)) {
            $date = gmdate('Y-m-d', ($date - 25569) * 86400);
        } else {
            $dateObj = DateTime::createFromFormat('d/m/Y', $date);
            $dtErrors = DateTime::getLastErrors();
            if (!$dateObj || ($dtErrors && ($dtErrors['warning_count'] > 0 || $dtErrors['error_count'] > 0))) {
                $errors[] = "Row $row: Invalid date '$date'. Expected DD/MM/YYYY.";
                continue;
            }
            $date = $dateObj->format('Y-m-d');
        }

        if ($date < $today) {
            $errors[] = "Row $row: Cannot import past date '$date'. Only today or future dates are allowed.";
            continue;
        }

        $outlet_id = (int)$outlet_arr[$code];
        $date_esc  = $conn->real_escape_string($date);

        $dup_key = $outlet_id . '|' . $date;
        if (isset($seen[$dup_key])) {
            $errors[] = "Row $row: Duplicate row in file for outlet $code on $date — skipped.";
            continue;
        }

        // Acknowledge = Yes -> Outlet Initiated (type 2), auto-acknowledged (status 1, no pending ack).
        // Acknowledge blank -> HQ Initiated (type 1), status 0 (pending outlet ack).
        $type            = $is_ack ? '2' : '1';
        $initial_status  = $is_ack ? '1' : '0';
        // Carepro Mobile Clinic = Yes -> Event Location auto-set to gp_clinics id 722.
        $clinic_id = $is_carepro ? 722 : 0;

        $seen[$dup_key] = true;

        $chk = mysqli_query($conn, "SELECT id, type, status, clinic FROM vaccine_campaign WHERE v_date='$date_esc' AND outlets='$outlet_id' AND recycle=0 ORDER BY id ASC LIMIT 1");
        $existing = $chk ? mysqli_fetch_assoc($chk) : null;
        if ($existing) {
            $ex_type   = (string) $existing['type'];
            $ex_status = (string) $existing['status'];
            $ex_clinic = (int) $existing['clinic'];

            // Cancelled campaigns (status 2) must be reverted from the campaign page, not by import.
            if ($ex_status === '2') {
                $errors[] = "Row $row: Campaign for outlet $code on $date is cancelled — not updated.";
                continue;
            }

            // Status only follows a type change; same type keeps current status so an
            // outlet acknowledgement on an HQ campaign is not reset back to pending.
            $new_status = ($ex_type !== $type) ? $initial_status : $ex_status;

            // Carepro = Yes forces clinic 722. Carepro blank only clears clinic when it is
            // currently 722; a manually assigned clinic is preserved.
            if ($is_carepro) {
                $new_clinic = 722;
            } elseif ($ex_clinic === 722) {
                $new_clinic = 0;
            } else {
                $new_clinic = $ex_clinic;
            }

            if ($ex_type === $type && $ex_status === $new_status && $ex_clinic === $new_clinic) {
                $unchanged++;
                continue;
            }

            $updates[] = [
                'id'     => (int) $existing['id'],
                'type'   => $type,
                'status' => $new_status,
                'clinic' => $new_clinic,
            ];
            continue;
        }

        $inserts[] = array(
            'date'      => $date_esc,
            'outlet_id' => $outlet_id,
            'type'      => $type,
            'status'    => $initial_status,
            'clinic'    => $clinic_id,
        );
    }

    $inserted = 0;
    $failed   = 0;
    $clinic_manual_needed = false;
    // Once saving starts, finish all writes even if the browser disconnects,
    // so the import is never left half-applied.
    ignore_user_abort(true);
    $total_writes = max(1, count($inserts) + count($updates));
    $written      = 0;
    $emit_progress(60, 'Saving campaigns...');
    foreach ($inserts as $row_data) {
        $written++;
        $emit_progress(60 + (int) floor($written / $total_writes * 40), "Saving campaign $written of $total_writes...");
        if ($row_data['clinic'] == 0) {
            $clinic_manual_needed = true;
        }
        $sql = "INSERT INTO vaccine_campaign (id, v_date, outlets, clinic, type, status) VALUES (NULL, '" . $row_data['date'] . "', '" . $row_data['outlet_id'] . "', '" . $row_data['clinic'] . "', '" . $row_data['type'] . "', '" . $row_data['status'] . "')";
        if (mysqli_query($conn, $sql)) {
            $inserted++;
        } else {
            $failed++;
        }
    }

    $updated       = 0;
    $update_failed = 0;
    foreach ($updates as $upd) {
        $written++;
        $emit_progress(60 + (int) floor($written / $total_writes * 40), "Saving campaign $written of $total_writes...");
        if ($upd['clinic'] == 0) {
            $clinic_manual_needed = true;
        }
        $sql = "UPDATE vaccine_campaign SET clinic='" . $upd['clinic'] . "', type='" . $upd['type'] . "', status='" . $upd['status'] . "' WHERE id='" . $upd['id'] . "' AND recycle=0";
        if (mysqli_query($conn, $sql)) {
            $updated++;
        } else {
            $update_failed++;
        }
    }

    $emit_progress(100, 'Completed.');
    if ($progress_file !== null) {
        @unlink($progress_file);
    }
    echo '<script type="text/javascript">impProgressDone();</script>' . "\n";
?>
<style type="text/css">
.idx-panel {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,.08);
    padding: 18px 22px;
    margin: 10px 0;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    text-align: left !important;
}
.idx-panel p, .idx-panel ul, .idx-panel li, .idx-panel div {
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    text-align: left !important;
}
.save-count {
    font-size: 15px !important;
    font-weight: bold !important;
    color: #005B96 !important;
    margin-bottom: 10px !important;
}
.save-errors {
    background: #fff5f5 !important;
    border-left: 3px solid #e53e3e !important;
    border-radius: 6px !important;
    padding: 10px 14px !important;
    margin: 10px 0 !important;
}
.save-errors p { color: #c53030 !important; font-weight: bold !important; margin: 0 0 6px 0 !important; }
.save-errors ul { margin: 0 !important; padding-left: 18px !important; }
.save-errors li { color: #c53030 !important; margin-bottom: 3px !important; }
.save-notice {
    background: #fffbeb !important;
    border-left: 3px solid #d97706 !important;
    border-radius: 6px !important;
    padding: 8px 14px !important;
    margin: 8px 0 !important;
    color: #92400e !important;
}
button.upd-submit, a.upd-submit, .upd-submit {
    display: inline-flex !important;
    align-items: center !important;
    background: #005B96 !important;
    color: #fff !important;
    border: 1px solid #005B96 !important;
    border-radius: 8px !important;
    height: 32px !important;
    padding: 5px 18px !important;
    font-weight: bold !important;
    cursor: pointer !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px !important;
    box-sizing: border-box !important;
    text-decoration: none !important;
    line-height: 1 !important;
}
button.upd-submit:hover, a.upd-submit:hover, .upd-submit:hover { background: #004d80 !important; border-color: #004d80 !important; }
a.upd-back, .upd-back {
    display: inline-flex !important;
    align-items: center !important;
    background: #e9ecef !important;
    color: #111 !important;
    border: 1px solid #d0d7de !important;
    border-radius: 8px !important;
    height: 32px !important;
    padding: 5px 18px !important;
    font-weight: bold !important;
    text-decoration: none !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px !important;
    box-sizing: border-box !important;
    line-height: 1 !important;
}
a.upd-back:hover, .upd-back:hover { background: #d8dde3 !important; border-color: #b0b8c1 !important; }
</style>
<div class="idx-panel">
    <div class="save-count"><?php echo $inserted; ?> campaign(s) imported, <?php echo $updated; ?> updated, <?php echo $unchanged; ?> unchanged.</div>
    <?php if ($failed > 0) { ?>
    <div class="save-errors">
        <p><?php echo $failed; ?> row(s) failed to insert.</p>
    </div>
    <?php } ?>
    <?php if ($update_failed > 0) { ?>
    <div class="save-errors">
        <p><?php echo $update_failed; ?> row(s) failed to update.</p>
    </div>
    <?php } ?>
    <?php if (!empty($errors)) { ?>
    <div class="save-errors">
        <p>Errors:</p>
        <ul>
            <?php foreach ($errors as $err) { echo "<li>" . htmlspecialchars($err) . "</li>"; } ?>
        </ul>
    </div>
    <?php } ?>
    <?php if (empty($inserts) && empty($updates) && $unchanged === 0) { ?>
    <div class="save-notice">No valid data found to import.</div>
    <?php } elseif ($clinic_manual_needed) { ?>
    <div class="save-notice">Some campaigns have no Event Location set. Please update each campaign's clinic manually.</div>
    <?php } ?>
    <div style="display:flex;align-items:center;gap:10px;margin-top:16px;">
        <a href="vaccine_campaign_import.php" class="upd-submit">Import Again</a>
        <a href="vaccine_calendar.php" class="upd-back">Back to Calendar</a>
    </div>
</div>
<?php
    $connect = 0;
    include('../common/index_adv.php');

} else {
?>
<style type="text/css">
.idx-panel {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,.08);
    padding: 14px 18px;
    margin: 6px 0 10px;
}
.myTable {
    width: 100% !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    border-collapse: collapse !important;
}
.myTable th, .myTable td {
    padding: 6px 10px !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    vertical-align: middle !important;
}
.myTable th {
    width: 160px !important;
    font-weight: bold !important;
    text-align: left !important;
    white-space: nowrap !important;
    background: transparent !important;
    color: inherit !important;
}
.myTable td { text-align: left !important; }
.myTable input[type="file"] {
    border-radius: 8px !important;
    padding: 4px 8px !important;
    border: 1px solid #cfcfcf !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    box-sizing: border-box !important;
    background: #fff !important;
    width: 100% !important;
    height: 34px !important;
    cursor: pointer !important;
}
.myTable input[type="file"]:focus {
    border-color: rgba(0,91,150,.55) !important;
    box-shadow: 0 0 0 3px rgba(0,91,150,.12) !important;
    outline: none !important;
}
.myTable input[type="file"]::file-selector-button {
    background: #005B96 !important;
    color: #fff !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 0 12px !important;
    font-size: 12px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-weight: bold !important;
    cursor: pointer !important;
    margin-right: 10px !important;
    height: 24px !important;
}
.myTable input[type="file"]::file-selector-button:hover {
    background: #004d80 !important;
}
button.upd-submit, a.upd-submit, .upd-submit {
    display: inline-flex !important;
    align-items: center !important;
    background: #005B96 !important;
    color: #fff !important;
    border: 1px solid #005B96 !important;
    border-radius: 8px !important;
    height: 32px !important;
    padding: 5px 18px !important;
    font-weight: bold !important;
    cursor: pointer !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px !important;
    box-sizing: border-box !important;
    text-decoration: none !important;
    line-height: 1 !important;
}
button.upd-submit:hover, a.upd-submit:hover, .upd-submit:hover { background: #004d80 !important; border-color: #004d80 !important; }
a.upd-back, .upd-back {
    display: inline-flex !important;
    align-items: center !important;
    background: #e9ecef !important;
    color: #111 !important;
    border: 1px solid #d0d7de !important;
    border-radius: 8px !important;
    height: 32px !important;
    padding: 5px 18px !important;
    font-weight: bold !important;
    text-decoration: none !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px !important;
    box-sizing: border-box !important;
    line-height: 1 !important;
}
a.upd-back:hover, .upd-back:hover { background: #d8dde3 !important; border-color: #b0b8c1 !important; }
.imp-progress{margin-top:12px;font-family:Arial,Helvetica,sans-serif !important;text-align:left !important;}
.imp-progress-label{font-size:13px !important;color:#111 !important;margin-bottom:8px !important;display:flex !important;justify-content:space-between !important;}
.imp-progress-track{background:#e9ecef;border-radius:8px;height:18px;overflow:hidden;}
.imp-progress-fill{background:#005B96;height:100%;width:0;transition:width .2s ease;}
</style>
<div class="idx-panel">
    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="impForm" onsubmit="return impUploadStart(this);">
        <table class="myTable">
            <tr>
                <th>Excel File (.xlsx) <span style="color:red;">*</span></th>
                <td>
                    <input name="userfile" type="file" required />
                    <div style="color:#6b7280 !important;font-size:12px !important;margin-top:4px !important;">Only campaigns for today or future dates can be imported. Past dates will be rejected.</div>
                </td>
            </tr>
            <tr>
                <th>Template</th>
                <td>
                    <a href="vaccine_campaign_template.xlsx" class="upd-back" title="Download Template">
                        <img src="../common/img/download.png" style="height:14px !important;width:auto !important;margin-right:4px !important;" /> Download Template
                    </a>
                </td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <div style="color:#6b7280 !important;font-size:12px !important;margin-bottom:8px !important;">Event Location is auto-set only if "Carepro Mobile Clinic" is marked Yes. Otherwise, update each campaign's clinic manually after import.</div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <button type="submit" name="submit" id="submit" class="upd-submit">Import</button>
                        <button type="button" id="impCancel" class="upd-back" style="display:none !important;cursor:pointer !important;" onclick="impUploadCancel();">Cancel</button>
                        <a href="vaccine_calendar.php" class="upd-back" id="impCalendarLink">Go to Calendar</a>
                    </div>
                    <div id="impCancelMsg" style="display:none;color:#92400e !important;font-size:12px !important;margin-top:8px !important;">Upload cancelled. No data was imported.</div>
                    <div id="impErrorMsg" style="display:none;color:#c53030 !important;font-size:12px !important;margin-top:8px !important;"></div>
                    <div class="imp-progress" id="impProgress" style="display:none;">
                        <div class="imp-progress-label"><span id="impProgressText">Uploading file...</span><span id="impProgressPct">0%</span></div>
                        <div class="imp-progress-track"><div class="imp-progress-fill" id="impProgressFill"></div></div>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</div>
<script type="text/javascript">
// The file is sent with XMLHttpRequest so the bar does not depend on the server
// streaming its response: upload progress comes from the browser (0-10%), and
// processing progress is polled from the server (10-100%).
var impXhr = null;
var impPollTimer = null;
var impShownPct = 0;

function impSetProgress(pct, text) {
    // Never move the bar backwards; a late poll response may carry an older value.
    if (pct < impShownPct) {
        return;
    }
    impShownPct = pct;
    document.getElementById('impProgressFill').style.width = pct + '%';
    document.getElementById('impProgressPct').textContent = pct + '%';
    document.getElementById('impProgressText').textContent = text;
}

function impNewToken() {
    var bytes = new Uint8Array(16);
    window.crypto.getRandomValues(bytes);
    return Array.prototype.map.call(bytes, function (x) { return ('0' + x.toString(16)).slice(-2); }).join('');
}

function impStopPolling() {
    if (impPollTimer !== null) {
        clearInterval(impPollTimer);
        impPollTimer = null;
    }
}

function impStartPolling(token) {
    impPollTimer = setInterval(function () {
        var p = new XMLHttpRequest();
        p.open('GET', 'vaccine_campaign_import.php?progress=' + token + '&_=' + Date.now(), true);
        p.onload = function () {
            if (p.status !== 200) {
                return;
            }
            try {
                var data = JSON.parse(p.responseText);
                if (data.pct > 0) {
                    impSetProgress(10 + Math.floor(data.pct * 0.9), data.text);
                }
            } catch (e) {
                // Snapshot was mid-write; the next poll picks it up.
            }
        };
        p.send();
    }, 700);
}

function impResetForm() {
    impStopPolling();
    impXhr = null;
    var b = document.getElementById('submit');
    b.textContent = 'Import';
    b.style.pointerEvents = '';
    b.style.opacity = '';
    document.getElementById('impCancel').style.setProperty('display', 'none', 'important');
    document.getElementById('impCalendarLink').style.removeProperty('display');
    document.getElementById('impProgress').style.display = 'none';
}

function impUploadStart(form) {
    if (!window.XMLHttpRequest || !window.FormData || !window.crypto) {
        return true; // Old browser: fall back to a normal form post.
    }

    var token = impNewToken();
    var fd = new FormData(form);
    fd.append('submit', '1');
    fd.append('imp_token', token);

    var b = document.getElementById('submit');
    b.textContent = 'Uploading...';
    b.style.pointerEvents = 'none';
    b.style.opacity = '.7';
    document.getElementById('impCancel').style.setProperty('display', 'inline-flex', 'important');
    document.getElementById('impCalendarLink').style.setProperty('display', 'none', 'important');
    document.getElementById('impCancelMsg').style.display = 'none';
    document.getElementById('impErrorMsg').style.display = 'none';
    document.getElementById('impProgress').style.display = 'block';
    impShownPct = 0;
    impSetProgress(0, 'Uploading file...');

    impXhr = new XMLHttpRequest();
    impXhr.open('POST', form.action, true);
    impXhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
            impSetProgress(Math.floor(e.loaded / e.total * 10), 'Uploading file...');
        }
    };
    impXhr.upload.onload = function () {
        // File is on the server; processing cannot be cancelled from here on.
        document.getElementById('impCancel').style.setProperty('display', 'none', 'important');
        b.textContent = 'Processing...';
        impSetProgress(10, 'Processing file...');
        impStartPolling(token);
    };
    impXhr.onload = function () {
        impStopPolling();
        if (impXhr.status === 200) {
            impSetProgress(100, 'Completed.');
            // Show the result page returned by the import.
            document.open();
            document.write(impXhr.responseText);
            document.close();
        } else {
            impResetForm();
            var err = document.getElementById('impErrorMsg');
            err.textContent = 'Import failed (HTTP ' + impXhr.status + '). Please try again.';
            err.style.display = 'block';
        }
    };
    impXhr.onerror = function () {
        impResetForm();
        var err = document.getElementById('impErrorMsg');
        err.textContent = 'Connection lost during import. Check the calendar before importing again.';
        err.style.display = 'block';
    };
    impXhr.send(fd);
    return false;
}

function impUploadCancel() {
    // Only offered while the file is still uploading, so nothing reaches the import.
    if (impXhr !== null) {
        impXhr.onerror = null;
        impXhr.abort();
    }
    impResetForm();
    document.getElementById('impCancelMsg').style.display = 'block';
}
</script>
<?php
    $connect = 0;
    include('../common/index_adv.php');
}
?>
