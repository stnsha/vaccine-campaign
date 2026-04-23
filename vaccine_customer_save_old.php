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

$ic            = trim(mysqli_real_escape_string($conn, $_POST['ic']));
$customer_name = trim(mysqli_real_escape_string($conn, $_POST['name']));
$customer_name = ucwords(strtolower($customer_name));
$birth_date    = trim(mysqli_real_escape_string($conn, $_POST['birth_date']));
$gender        = trim(mysqli_real_escape_string($conn, $_POST['gender']));
$phone         = preg_replace('/\D/', '', trim(mysqli_real_escape_string($conn, $_POST['phone'])));
$child_num     = trim(mysqli_real_escape_string($conn, $_POST['child_num']));
$language      = trim(mysqli_real_escape_string($conn, $_POST['language']));
$race          = trim(mysqli_real_escape_string($conn, $_POST['race']));
$nationality   = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['nationality'])));
$email         = trim(mysqli_real_escape_string($conn, $_POST['email']));
$c_addr        = trim(mysqli_real_escape_string($conn, $_POST['addr']));

if (!empty($child_num)) { $phone = $phone . "@$child_num"; }

if (!$ic || !$customer_name || !$race || !$nationality) {
    json_err('Please fill in all required fields.');
}

$chk = mysqli_query($conn, "SELECT id FROM customer WHERE ic='$ic' AND recycle=0 LIMIT 1");
if (mysqli_num_rows($chk) > 0) {
    json_err('A customer with this IC already exists.');
}

$query = "INSERT INTO customer (id, c_id, date, customer_name, ic, gender, birth_date, allergic, diagnosis, language, phone, email, c_addr, operator, race, nationality, recycle)
          VALUES (NULL, '', NOW(), '$customer_name', '$ic', '$gender', '$birth_date', '', '', '$language', '$phone', '$email', '$c_addr', '$id_user', '$race', '$nationality', '0')";
$result = mysqli_query($conn, $query);

if (!$result) { json_err('Database error: ' . mysqli_error($conn)); }

$phone_digits = preg_replace('/\D/', '', explode('@', $phone)[0]);
$child_out    = (strpos($phone, '@') !== false) ? explode('@', $phone)[1] : '';

echo json_encode(array(
    'success'   => true,
    'ic'        => $ic,
    'name'      => stripslashes($customer_name),
    'phone'     => $phone_digits,
    'child_num' => $child_out
));

mysqli_close($conn);
?>
