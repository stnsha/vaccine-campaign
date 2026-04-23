<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
require_once('../lock_adv.php');
$connect = 1;
include('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');

if (isset($_POST['submit'])) {

    // Build outlet lookup: code => id
    $query_outlet = "SELECT `id`, `code` FROM `outlet` WHERE recycle = 0";
    $result_outlet = mysqli_query($conn, $query_outlet);
    $outlet_arr = array();
    while ($row_outlet = $result_outlet->fetch_assoc()) {
        $outlet_arr[stripslashes($row_outlet['code'])] = stripslashes($row_outlet['id']);
    }

    $fileName = $_FILES['userfile']['name'];
    $tmpName  = $_FILES['userfile']['tmp_name'];
    $ext      = strtolower(substr(strrchr($fileName, '.'), 1));

    if ($ext != 'xlsx') {
        echo "<fieldset class='center'><img src='../common/img/warning.png'><br/>Unsupported file type!<br/><a href='vaccine_campaign_import.php'><img src='../common/img/refresh.png'> Back</a></fieldset>";
        $connect = 0;
        include('../common/index_adv.php');
        exit;
    }

    ini_set('memory_limit', '2000M');
    ini_set('max_execution_time', 30000);

    require_once('../common/PHPexcel/PHPExcel/IOFactory.php');

    try {
        $inputFileType = PHPExcel_IOFactory::identify($tmpName);
        $objReader     = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel   = $objReader->load($tmpName);
    } catch (Exception $e) {
        die('Error loading file "' . pathinfo($fileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    $sheet         = $objPHPExcel->getSheet(0);
    $highestRow    = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    $errors  = array();
    $inserts = array();
    $today   = date('Y-m-d');

    for ($row = 3; $row <= $highestRow; $row++) {
        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

        $date = trim($rowData[0][0]);
        $code = trim($rowData[0][1]);

        if (empty($date)) {
            $errors[] = "Row $row: Missing date";
            continue;
        }

        if (empty($code)) {
            $errors[] = "Row $row: Missing outlet code";
            continue;
        }

        if (!isset($outlet_arr[$code])) {
            $errors[] = "Row $row: Outlet code '<b>$code</b>' not found in system";
            continue;
        }

        if (is_numeric($date)) {
            $date = gmdate('Y-m-d', ($date - 25569) * 86400);
        } else {
            $date = date('Y-m-d', strtotime($date));
        }

        if ($date < $today) {
            $errors[] = "Row $row: Cannot import past date '<b>$date</b>'. Only today or future dates are allowed.";
            continue;
        }

        $outlet_id = (int)$outlet_arr[$code];
        $date_esc  = $conn->real_escape_string($date);

        // Skip if campaign already exists for this outlet + date
        $chk = mysqli_query($conn, "SELECT id FROM vaccine_campaign WHERE v_date = '$date_esc' AND outlets = '$outlet_id' LIMIT 1");
        if (mysqli_fetch_assoc($chk)) {
            $errors[] = "Row $row: Campaign for outlet <b>$code</b> on <b>$date</b> already exists — skipped.";
            continue;
        }

        $inserts[] = array('date' => $date_esc, 'outlet_id' => $outlet_id);
    }

    echo "<fieldset>";

    if (!empty($errors)) {
        echo "<div style='color: red; margin-bottom: 15px;'>";
        echo "<b>Errors Found:</b><br/>";
        foreach ($errors as $error) {
            echo "- $error<br/>";
        }
        echo "</div>";
    }

    if (!empty($inserts)) {
        $inserted = 0;
        $failed   = 0;
        foreach ($inserts as $row_data) {
            $sql = "INSERT INTO vaccine_campaign (id, v_date, outlets, clinic, type, status) VALUES (NULL, '" . $row_data['date'] . "', '" . $row_data['outlet_id'] . "', '0', '1', '0')";
            if (mysqli_query($conn, $sql)) {
                $inserted++;
            } else {
                $failed++;
            }
        }
        echo "<div style='color: green;'><b>Successfully inserted $inserted campaign(s).</b></div>";
        if ($failed > 0) {
            echo "<div style='color: red;'><b>$failed row(s) failed to insert.</b></div>";
        }
        echo "<div style='color: blue;'>Clinic is set to 0. Please update each campaign's clinic manually.</div><br/>";
    } else {
        echo "<div style='color: orange;'><b>No valid data found to insert.</b></div><br/>";
    }

    echo "<a href='vaccine_calendar.php'>Back to Calendar</a>";
    echo "</fieldset>";

    $connect = 0;
    include('../common/index_adv.php');

} else {
?>
<div class="header" style="position: relative;">
    <b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
    <h1 class="headerH1"><img src='../common/img/target.png' width='20px'> Import Vaccine Campaign</h1>
    <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<fieldset>
    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <table width="100%" border="0" cellspacing="2" cellpadding="0">
            <tr>
                <td>
                    <div align="right"><b>Excel File (.xlsx):</b></div>
                </td>
                <td>
                    <input name="userfile" type="file" required>
                    <br/>
                    <small style="color: #666;">Only campaigns for today or future dates can be imported. Past dates will be rejected.</small>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <a href='vaccine_campaign_template.xlsx' title='Download Template'><img src='../common/img/download.png'> Download Template</a>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <small style="color: #888;">Clinic will be set to 0. Update each campaign's clinic manually after import.</small>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input name="submit" type="submit" class="box" id="submit" value="submit">
                </td>
            </tr>
        </table>
    </form>
</fieldset>
<?php
    $connect = 0;
    include('../common/index_adv.php');
}
?>
