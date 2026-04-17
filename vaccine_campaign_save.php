<?php
ob_start();
require_once('../lock_adv.php');
$connect = 1;
include('../common/index_adv.php');
ob_end_clean();
header('Content-Type: application/json');

function json_err($msg) {
    echo json_encode(array('success' => false, 'error' => $msg));
    exit;
}

$outlet_id = trim(mysqli_real_escape_string($conn, $_POST['outlet_id']));
$v_date    = trim(mysqli_real_escape_string($conn, $_POST['v_date']));
$clinic_id = (int)$_POST['clinic_id'];

if (!$outlet_id || !$v_date || $clinic_id == 0) {
    json_err('Missing required fields.');
}

// Check if campaign already exists
$chk = mysqli_query($conn, "SELECT id FROM vaccine_campaign WHERE outlets='$outlet_id' AND v_date='$v_date' LIMIT 1");
if (mysqli_num_rows($chk) > 0) {
    $row = mysqli_fetch_assoc($chk);
    echo json_encode(array('success' => true, 'id' => $row['id']));
    mysqli_close($conn); exit;
}

$query = "INSERT INTO vaccine_campaign (id, v_date, outlets, clinic, type, status) VALUES (NULL, '$v_date', '$outlet_id', '$clinic_id', '2', '1')";
$result = mysqli_query($conn, $query);

if (!$result) {
    json_err('Database error: ' . mysqli_error($conn));
}

$new_id = mysqli_insert_id($conn);
echo json_encode(array('success' => true, 'id' => $new_id));

mysqli_close($conn);
?>
