<?php
require_once('vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

require_once('../lock_adv.php');
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
    $today   = date('Y-m-d');

    for ($row = 3; $row <= $highestRow; $row++) {
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

        $chk = mysqli_query($conn, "SELECT id FROM vaccine_campaign WHERE v_date='$date_esc' AND outlets='$outlet_id' AND recycle=0 LIMIT 1");
        if ($chk && mysqli_fetch_assoc($chk)) {
            $errors[] = "Row $row: Campaign for outlet $code on $date already exists — skipped.";
            continue;
        }

        // Acknowledge = Yes -> Outlet Initiated (type 2), auto-acknowledged (status 1, no pending ack).
        // Acknowledge blank -> HQ Initiated (type 1), status 0 (pending outlet ack).
        $type            = $is_ack ? '2' : '1';
        $initial_status  = $is_ack ? '1' : '0';
        // Carepro Mobile Clinic = Yes -> Event Location auto-set to gp_clinics id 722.
        $clinic_id = $is_carepro ? 722 : 0;

        $seen[$dup_key] = true;
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
    foreach ($inserts as $row_data) {
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
    <div class="save-count"><?php echo $inserted; ?> campaign(s) imported successfully.</div>
    <?php if ($failed > 0) { ?>
    <div class="save-errors">
        <p><?php echo $failed; ?> row(s) failed to insert.</p>
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
    <?php if (empty($inserts)) { ?>
    <div class="save-notice">No valid data found to insert.</div>
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
</style>
<div class="idx-panel">
    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>">
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
                        <a href="vaccine_calendar.php" class="upd-back">Go to Calendar</a>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</div>
<?php
    $connect = 0;
    include('../common/index_adv.php');
}
?>
