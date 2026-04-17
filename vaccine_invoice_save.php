<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
require_once('../lock_adv.php');
$connect = 1;
include('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_POST['submit'])) {
    header('location: vaccine_invoice.php');
    exit;
}

$rows        = isset($_POST['rows'])        ? $_POST['rows']        : array();
$campaign_id = isset($_POST['campaign_id']) ? (int)$_POST['campaign_id'] : 0;
$added       = 0;
$errors      = array();

foreach ($rows as $idx => $row) {
    $outlet_id     = trim(mysqli_real_escape_string($conn, $row['outlet_id']));
    $inv_num       = trim(mysqli_real_escape_string($conn, $row['inv_num']));
    $v_date        = trim(mysqli_real_escape_string($conn, $row['v_date']));
    $item_code     = trim(mysqli_real_escape_string($conn, $row['item_code']));
    $cust_ic       = trim(mysqli_real_escape_string($conn, $row['cust_ic']));
    $customer_name = trim(mysqli_real_escape_string($conn, $row['customer_name']));
    $phone2        = trim(mysqli_real_escape_string($conn, $row['phone2']));
    $child_num     = trim(mysqli_real_escape_string($conn, $row['child_num']));
    $remark        = trim(mysqli_real_escape_string($conn, $row['remark']));
    $clinic        = trim(mysqli_real_escape_string($conn, $row['clinic']));
    $clinic_parts  = explode(":", $clinic);
    $clinic_id     = trim($clinic_parts[0]);

    if ((int)$clinic_id == 0) {
        $errors[] = "Row $idx: Please select clinic from dropdown list.";
        continue;
    }

    if (!$cust_ic || !$customer_name) {
        $errors[] = "Row $idx: Customer not selected.";
        continue;
    }

    if (!empty($child_num)) { $child_num = "@$child_num"; } else { $child_num = ''; }
    $phone2 = preg_replace('/\D/', '', $phone2);
    $phone2 = "$phone2$child_num";

    // Update customer phone
    $sql4 = "UPDATE customer SET phone='$phone2' WHERE customer_name='$customer_name' AND ic='$cust_ic'";
    mysqli_query($conn, $sql4);

    // Get customer ID
    $sql3 = "SELECT id FROM customer WHERE ic='$cust_ic' AND customer_name='$customer_name' AND recycle=0";
    $result3 = mysqli_query($conn, $sql3);
    $row3 = $result3->fetch_assoc();
    $cust_id = $row3 ? stripslashes($row3['id']) : '';

    if (!$cust_id) {
        $errors[] = "Row $idx: Customer not found in system.";
        continue;
    }

    // Check duplicate
    $sql2 = "SELECT id FROM vaccine_trans WHERE outlet_id='$outlet_id' AND v_date='$v_date' AND cust_id='$cust_id' AND item_code='$item_code' AND recycle=0";
    $result2 = mysqli_query($conn, $sql2);
    if (mysqli_num_rows($result2) > 0) {
        $errors[] = "Row $idx: Duplicate transaction for customer $customer_name.";
        continue;
    }

    // Get or create campaign for this outlet+date
    $camp_chk = "SELECT id FROM vaccine_campaign WHERE outlets='$outlet_id' AND v_date='$v_date' LIMIT 1";
    $camp_r   = mysqli_query($conn, $camp_chk);
    $camp_row = $camp_r ? mysqli_fetch_assoc($camp_r) : null;
    if ($camp_row) {
        $campaign_id = (int)$camp_row['id'];
    } else {
        $camp_ins = "INSERT INTO vaccine_campaign (id, v_date, outlets, clinic, type, status) VALUES (NULL, '$v_date', '$outlet_id', '$clinic_id', '2', '1')";
        mysqli_query($conn, $camp_ins);
        $campaign_id = (int)mysqli_insert_id($conn);
    }

    // Insert transaction (default time 00:00)
    $query = "INSERT INTO vaccine_trans (id, timestamp, v_date, cust_id, item_code, clinic, outlet_id, remark, status, operator, inv_num, campaign_id) VALUES (NULL, NOW(), '$v_date 00:00:00', '$cust_id', '$item_code', '$clinic_id', '$outlet_id', '$remark', '0', '$id_user', '$inv_num', '$campaign_id')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $added++;
    }
}
?>
<div class="header" style="position:relative;">
    <b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
    <h1 class="headerH1"><img src='../common/img/vaccine.png' height='18px'> Save Result</h1>
    <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<fieldset>
    <p><b><?php echo $added; ?></b> transaction(s) saved successfully.</p>
    <?php if (!empty($errors)) { ?>
    <p style="color:red;"><b>Errors:</b></p>
    <ul>
        <?php foreach ($errors as $e) { echo "<li>" . htmlspecialchars($e) . "</li>"; } ?>
    </ul>
    <?php } ?>
    <p>
        <a href="vaccine_invoice.php">Enter New Invoice</a> &nbsp;|&nbsp;
        <a href="vaccine_index.php">View Transactions</a>
        <?php if ($campaign_id > 0) { ?>
        &nbsp;|&nbsp; <a href="vaccine_campaign.php?id=<?php echo $campaign_id; ?>">Go to Campaign</a>
        <?php } ?>
    </p>
</fieldset>
<?php
$connect = 0;
include('../common/index_adv.php');
?>
