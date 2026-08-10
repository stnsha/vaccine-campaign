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
    $outlet_id     = trim(mysqli_real_escape_string($conn, $row['outlet_id'] ?? ''));
    $inv_num       = trim(mysqli_real_escape_string($conn, $row['inv_num'] ?? ''));
    $v_date        = trim(mysqli_real_escape_string($conn, $row['v_date'] ?? ''));
    $item_code     = trim(mysqli_real_escape_string($conn, $row['item_code'] ?? ''));
    $cust_ic       = trim(mysqli_real_escape_string($conn, $row['cust_ic'] ?? ''));
    $customer_name = trim(mysqli_real_escape_string($conn, $row['customer_name'] ?? ''));
    $phone2        = trim(mysqli_real_escape_string($conn, $row['phone2'] ?? ''));
    $child_num     = trim(mysqli_real_escape_string($conn, $row['child_num'] ?? ''));
    $remark        = trim(mysqli_real_escape_string($conn, $row['remark'] ?? ''));
    $clinic        = trim(mysqli_real_escape_string($conn, $row['clinic'] ?? ''));
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
    $sql4 = "UPDATE customer SET phone='$phone2' WHERE ic='$cust_ic' AND recycle=0";
    mysqli_query($conn, $sql4);

    // Get customer ID
    $sql3 = "SELECT id FROM customer WHERE ic='$cust_ic' AND recycle=0 LIMIT 1";
    $result3 = mysqli_query($conn, $sql3);
    $row3 = $result3 ? $result3->fetch_assoc() : null;
    $cust_id = $row3 ? (int)($row3['id'] ?? 0) : 0;

    if (!$cust_id || $cust_id == 0) {
        $errors[] = "Row $idx: Customer not found in system.";
        continue;
    }

    // Check duplicate
    $sql2 = "SELECT id FROM vaccine_trans WHERE outlet_id='$outlet_id' AND v_date='$v_date' AND cust_id='$cust_id' AND item_code='$item_code' AND recycle=0";
    $result2 = mysqli_query($conn, $sql2);
    if ($result2 && mysqli_num_rows($result2) > 0) {
        $errors[] = "Row $idx: Duplicate transaction for customer $customer_name.";
        continue;
    }

    // Get or create campaign for this outlet+date
    $camp_chk = "SELECT id FROM vaccine_campaign WHERE outlets='$outlet_id' AND v_date='$v_date' AND recycle=0 LIMIT 1";
    $camp_r   = mysqli_query($conn, $camp_chk);
    $camp_row = $camp_r ? mysqli_fetch_assoc($camp_r) : null;
    if ($camp_row) {
        $campaign_id = (int)($camp_row['id'] ?? 0);
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
    font-weight: bold;
    color: #005B96;
    margin-bottom: 10px;
}
.save-errors {
    background: #fff5f5;
    border-left: 3px solid #e53e3e;
    border-radius: 6px;
    padding: 10px 14px;
    margin: 10px 0;
}
.save-errors p {
    color: #c53030 !important;
    font-weight: bold;
    margin: 0 0 6px 0;
    text-align: left !important;
}
.save-errors ul {
    margin: 0;
    padding-left: 18px;
    text-align: left !important;
}
.save-errors li {
    color: #c53030 !important;
    margin-bottom: 3px;
    text-align: left !important;
}
.save-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 16px;
}
button.upd-submit, .upd-submit {
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
button.upd-submit:hover, .upd-submit:hover { background: #004d80 !important; border-color: #004d80 !important; }
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
    <div class="save-count">
        <?php echo $added; ?> transaction(s) saved successfully.
    </div>
    <?php if (!empty($errors)) { ?>
    <div class="save-errors">
        <p>Errors:</p>
        <ul>
            <?php foreach ($errors as $e) { echo "<li>" . htmlspecialchars($e) . "</li>"; } ?>
        </ul>
    </div>
    <?php } ?>
    <div class="save-actions">
        <a href="vaccine_invoice.php" class="upd-submit">Enter New Invoice</a>
        <a href="vaccine_index.php" class="upd-back">View Transactions</a>
        <?php if ($campaign_id > 0) { ?>
        <a href="vaccine_campaign.php?id=<?php echo $campaign_id; ?>" class="upd-back">Go to Campaign</a>
        <?php } ?>
    </div>
</div>
<?php
$connect = 0;
include('../common/index_adv.php');
?>
