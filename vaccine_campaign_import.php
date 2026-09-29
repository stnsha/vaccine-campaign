<?php
require_once('vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

require_once('../lock_adv.php');
// Release the session lock right after the login check. A long import would otherwise
// block progress polling and every other page opened with the same session.
session_write_close();

// Temp files shared between the running import and the polling/cancel requests.
$imp_temp_path = fn (string $token, string $ext): string => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vc_import_' . $token . '.' . $ext;

// lock_adv.php prints page markup before the login check finishes. Drop whatever is still
// buffered and mark where the JSON starts, so the page can read it even when some of that
// markup was already sent.
$send_json = function (string $json): never {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
    }
    echo "
@@IMP_JSON@@" . $json;
    exit;
};

// Progress polling endpoint: returns the snapshot written by a running import.
if (isset($_GET['progress'])) {
    $poll_token = preg_replace('/[^a-f0-9]/', '', (string) $_GET['progress']);
    $poll_file  = $imp_temp_path($poll_token, 'json');
    $snapshot   = ($poll_token !== '' && is_file($poll_file)) ? @file_get_contents($poll_file) : false;
    $send_json($snapshot !== false && $snapshot !== '' ? $snapshot : '{"pct":0,"text":"Waiting for server...","cancellable":true}');
}

// Cancel endpoint: flags a running import to stop. The import honours the flag only
// until it starts saving, so a cancelled import never leaves partial data behind.
if (isset($_GET['cancel'])) {
    $cancel_token = preg_replace('/[^a-f0-9]/', '', (string) $_GET['cancel']);
    if ($cancel_token !== '') {
        @touch($imp_temp_path($cancel_token, 'cancel'));
    }
    $send_json('{"ok":true}');
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
    $progress_file = $imp_token !== '' ? $imp_temp_path($imp_token, 'json') : null;
    $cancel_file   = $imp_token !== '' ? $imp_temp_path($imp_token, 'cancel') : null;

    // Cancel is allowed while loading and validating; it is switched off once saving starts.
    $cancellable = true;

    // Import log shown live on the upload page and in full on the result page.
    $log_lines     = [];
    $last_pct      = -1;
    $last_text     = '';
    $last_write_at = 0.0;

    // Writes the progress snapshot polled by the upload page. Only the latest log lines are
    // included; log_total lets the page work out which lines it has not shown yet.
    $write_snapshot = function (bool $force) use (&$log_lines, &$last_pct, &$last_text, &$last_write_at, &$cancellable, $progress_file): void {
        if ($progress_file === null) {
            return;
        }
        $now = microtime(true);
        if (!$force && $now - $last_write_at < 0.3) {
            return;
        }
        $last_write_at = $now;
        @file_put_contents($progress_file, json_encode([
            'pct'         => max(0, $last_pct),
            'text'        => $last_text,
            'cancellable' => $cancellable,
            'log_total'   => count($log_lines),
            'log'         => array_slice($log_lines, -300),
        ]), LOCK_EX);
    };

    $add_log = function (string $level, string $message) use (&$log_lines, $write_snapshot): void {
        $log_lines[] = ['t' => date('H:i:s'), 'level' => $level, 'msg' => $message];
        $write_snapshot(false);
    };

    $emit_progress = function (int $pct, string $text) use (&$last_pct, &$last_text, $write_snapshot): void {
        if ($pct === $last_pct) {
            return;
        }
        $last_pct  = $pct;
        $last_text = $text;
        $write_snapshot(true);
        echo '<script type="text/javascript">impProgress(' . $pct . ', ' . json_encode($text) . ');</script>' . "\n";
        flush();
    };

    $cleanup_temp = function () use ($progress_file, $cancel_file): void {
        foreach ([$progress_file, $cancel_file] as $f) {
            if ($f !== null && is_file($f)) {
                @unlink($f);
            }
        }
    };

    $is_cancelled = function () use ($cancel_file): bool {
        if ($cancel_file === null) {
            return false;
        }
        clearstatcache(true, $cancel_file);
        return is_file($cancel_file);
    };

    // Stops the import before anything is written and shows the cancelled notice.
    $stop_cancelled = function () use ($cleanup_temp): never {
        $cleanup_temp();
        echo '<script type="text/javascript">impProgressDone();</script>' . "\n";
?>
<style type="text/css">
.idx-panel{background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:18px 22px;margin:10px 0;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;text-align:left !important;}
a.upd-back,.upd-back{display:inline-flex !important;align-items:center !important;background:#e9ecef !important;color:#111 !important;border:1px solid #d0d7de !important;border-radius:8px !important;height:32px !important;padding:5px 18px !important;font-weight:bold !important;text-decoration:none !important;font-family:Arial,Helvetica,sans-serif !important;font-size:13px !important;box-sizing:border-box !important;line-height:1 !important;}
</style>
<div class="idx-panel">
    <p style="color:#92400e !important;font-size:13px !important;">Import cancelled. No data was imported.</p>
    <a href="vaccine_campaign_import.php" class="upd-back">Go Back</a>
</div>
<?php
        $connect = 0;
        include('../common/index_adv.php');
        exit;
    };

    $add_log('info', 'Import started: ' . pathinfo($fileName, PATHINFO_BASENAME));
    $emit_progress(2, 'Loading file...');

    try {
        $inputFileType = IOFactory::identify($tmpName);
        $objReader     = IOFactory::createReader($inputFileType);
        $objPHPExcel   = $objReader->load($tmpName);
    } catch (Exception $e) {
        $cleanup_temp();
        die('Error loading file "' . pathinfo($fileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    if ($is_cancelled()) {
        $stop_cancelled();
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

    // Records a rejected row in both the error list and the live log.
    $reject = function (string $message) use (&$errors, $add_log): void {
        $errors[] = $message;
        $add_log('warn', $message);
    };

    // Validation covers 5-60%, database writes cover 60-100%.
    $total_rows = max(1, $highestRow - 2);
    $add_log('info', "File loaded: $total_rows data row(s) found.");
    $emit_progress(5, 'Validating rows...');

    for ($row = 3; $row <= $highestRow; $row++) {
        if ($is_cancelled()) {
            $add_log('warn', 'Import cancelled by user during validation.');
            $stop_cancelled();
        }

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
            $reject("Row $row: Missing date.");
            continue;
        }
        if (empty($code)) {
            $reject("Row $row: Missing outlet code.");
            continue;
        }
        if (!isset($outlet_arr[$code])) {
            $reject("Row $row: Outlet code '$code' not found in system.");
            continue;
        }

        if (is_numeric($date)) {
            $date = gmdate('Y-m-d', ($date - 25569) * 86400);
        } else {
            $dateObj = DateTime::createFromFormat('d/m/Y', $date);
            $dtErrors = DateTime::getLastErrors();
            if (!$dateObj || ($dtErrors && ($dtErrors['warning_count'] > 0 || $dtErrors['error_count'] > 0))) {
                $reject("Row $row: Invalid date '$date'. Expected DD/MM/YYYY.");
                continue;
            }
            $date = $dateObj->format('Y-m-d');
        }

        if ($date < $today) {
            $reject("Row $row: Cannot import past date '$date'. Only today or future dates are allowed.");
            continue;
        }

        $outlet_id = (int)$outlet_arr[$code];
        $date_esc  = $conn->real_escape_string($date);

        $dup_key = $outlet_id . '|' . $date;
        if (isset($seen[$dup_key])) {
            $reject("Row $row: Duplicate row in file for outlet $code on $date — skipped.");
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
                $reject("Row $row: Campaign for outlet $code on $date is cancelled — not updated.");
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
                $add_log('info', "Row $row: Outlet $code on $date already up to date.");
                continue;
            }

            $updates[] = [
                'id'     => (int) $existing['id'],
                'type'   => $type,
                'status' => $new_status,
                'clinic' => $new_clinic,
                'row'    => $row,
                'code'   => $code,
                'date'   => $date,
            ];
            $add_log('info', "Row $row: Outlet $code on $date will be updated.");
            continue;
        }

        $inserts[] = array(
            'date'      => $date_esc,
            'outlet_id' => $outlet_id,
            'type'      => $type,
            'status'    => $initial_status,
            'clinic'    => $clinic_id,
            'row'       => $row,
            'code'      => $code,
        );
        $add_log('info', "Row $row: New campaign for outlet $code on $date.");
    }

    $add_log('info', 'Validation finished: ' . count($inserts) . ' new, ' . count($updates) . ' to update, ' . $unchanged . ' unchanged, ' . count($errors) . ' rejected.');

    // Last chance to cancel. After this point every write runs to the end so the
    // import is never left half-applied.
    if ($is_cancelled()) {
        $add_log('warn', 'Import cancelled by user before saving.');
        $stop_cancelled();
    }
    $cancellable = false;

    $inserted = 0;
    $failed   = 0;
    $clinic_manual_needed = false;
    // Once saving starts, finish all writes even if the browser disconnects.
    ignore_user_abort(true);
    $total_writes = max(1, count($inserts) + count($updates));
    $written      = 0;
    $add_log('info', 'Saving started. Cancel is no longer available.');
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
            $add_log('ok', "Row {$row_data['row']}: Created campaign for outlet {$row_data['code']} on {$row_data['date']}.");
        } else {
            $failed++;
            $add_log('error', "Row {$row_data['row']}: Insert failed for outlet {$row_data['code']} on {$row_data['date']}: " . mysqli_error($conn));
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
            $add_log('ok', "Row {$upd['row']}: Updated campaign for outlet {$upd['code']} on {$upd['date']}.");
        } else {
            $update_failed++;
            $add_log('error', "Row {$upd['row']}: Update failed for outlet {$upd['code']} on {$upd['date']}: " . mysqli_error($conn));
        }
    }

    if ($is_cancelled()) {
        $add_log('warn', 'Cancel request arrived after saving started; the import was completed.');
    }
    $add_log('info', "Import finished: $inserted created, $updated updated, $unchanged unchanged, " . ($failed + $update_failed) . ' failed.');
    $emit_progress(100, 'Completed.');
    $cleanup_temp();
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
.imp-log-title{font-weight:bold !important;margin:14px 0 6px !important;color:#111 !important;}
.imp-log{background:#1e1e1e;border-radius:8px;padding:8px 10px;max-height:260px;overflow-y:auto;font-family:Consolas,'Courier New',monospace !important;font-size:12px !important;line-height:1.5 !important;}
.imp-log div{font-family:Consolas,'Courier New',monospace !important;font-size:12px !important;white-space:pre-wrap !important;word-break:break-word !important;}
.imp-log .lv-info{color:#d4d4d4 !important;}
.imp-log .lv-ok{color:#6ee7a0 !important;}
.imp-log .lv-warn{color:#fbbf24 !important;}
.imp-log .lv-error{color:#f87171 !important;}
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
    <div class="imp-log-title">Import Log (<?php echo count($log_lines); ?> entries)</div>
    <div class="imp-log">
        <?php foreach ($log_lines as $line) { ?>
        <div class="lv-<?php echo $line['level']; ?>">[<?php echo $line['t']; ?>] <?php echo htmlspecialchars($line['msg']); ?></div>
        <?php } ?>
    </div>
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
.imp-log{background:#1e1e1e;border-radius:8px;padding:8px 10px;margin-top:10px;max-height:220px;overflow-y:auto;font-family:Consolas,'Courier New',monospace !important;font-size:12px !important;line-height:1.5 !important;}
.imp-log:empty{display:none;}
.imp-log div{font-family:Consolas,'Courier New',monospace !important;font-size:12px !important;white-space:pre-wrap !important;word-break:break-word !important;}
.imp-log .lv-info{color:#d4d4d4 !important;}
.imp-log .lv-ok{color:#6ee7a0 !important;}
.imp-log .lv-warn{color:#fbbf24 !important;}
.imp-log .lv-error{color:#f87171 !important;}
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
                        <div class="imp-log" id="impLog"></div>
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
var impToken = '';
var impUploaded = false;
var impCancelling = false;
var impLogCount = 0;

function impAppendLog(total, lines) {
    // The snapshot carries only the latest lines; skip the ones already shown.
    var box = document.getElementById('impLog');
    var atBottom = box.scrollTop + box.clientHeight >= box.scrollHeight - 5;
    var firstIndex = total - lines.length;
    for (var i = 0; i < lines.length; i++) {
        if (firstIndex + i < impLogCount) {
            continue;
        }
        var div = document.createElement('div');
        div.className = 'lv-' + lines[i].level;
        div.textContent = '[' + lines[i].t + '] ' + lines[i].msg;
        box.appendChild(div);
    }
    impLogCount = Math.max(impLogCount, total);
    if (atBottom) {
        box.scrollTop = box.scrollHeight;
    }
}

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
                // Read only what follows the marker; anything before it is page markup.
                var raw = p.responseText;
                var mark = raw.lastIndexOf('@@IMP_JSON@@');
                var data = JSON.parse(mark >= 0 ? raw.substring(mark + 12) : raw);
                if (data.pct > 0) {
                    impSetProgress(10 + Math.floor(data.pct * 0.9), data.text);
                }
                if (data.log) {
                    impAppendLog(data.log_total, data.log);
                }
                // Saving has started on the server: cancelling is no longer possible.
                if (data.cancellable === false) {
                    document.getElementById('impCancel').style.setProperty('display', 'none', 'important');
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
    impUploaded = false;
    impCancelling = false;
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
    impToken = token;
    impUploaded = false;
    impCancelling = false;
    impLogCount = 0;
    document.getElementById('impLog').innerHTML = '';
    var c = document.getElementById('impCancel');
    c.textContent = 'Cancel';
    c.style.pointerEvents = '';
    c.style.opacity = '';
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
        // File is on the server; from here Cancel asks the server to stop instead of
        // aborting the request, and stays available until saving starts.
        impUploaded = true;
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
    if (impCancelling) {
        return;
    }
    if (!impUploaded) {
        // Still uploading: aborting the request means the file never reaches the import.
        if (impXhr !== null) {
            impXhr.onerror = null;
            impXhr.abort();
        }
        impResetForm();
        document.getElementById('impCancelMsg').style.display = 'block';
        return;
    }

    // Processing: flag the import to stop. The server answers the running request with a
    // cancelled page, or with the full result if saving had already started.
    impCancelling = true;
    var c = document.getElementById('impCancel');
    c.textContent = 'Cancelling...';
    c.style.pointerEvents = 'none';
    c.style.opacity = '.7';
    var q = new XMLHttpRequest();
    q.open('GET', 'vaccine_campaign_import.php?cancel=' + impToken + '&_=' + Date.now(), true);
    q.send();
}
</script>
<?php
    $connect = 0;
    include('../common/index_adv.php');
}
?>
