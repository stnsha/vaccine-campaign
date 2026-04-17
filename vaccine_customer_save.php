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

$customer_name = trim(mysqli_real_escape_string($conn, $_POST['name']));
$customer_name = ucwords(strtolower($customer_name));
$ic1           = trim(mysqli_real_escape_string($conn, $_POST['ic1']));
$ic2           = trim(mysqli_real_escape_string($conn, $_POST['ic2']));
$ic3           = trim(mysqli_real_escape_string($conn, $_POST['ic3']));
$ic            = "$ic1$ic2$ic3";
$phone         = preg_replace('/\D/', '', trim(mysqli_real_escape_string($conn, $_POST['phone'])));
$child_num     = trim(mysqli_real_escape_string($conn, $_POST['child_num']));
$language      = trim(mysqli_real_escape_string($conn, $_POST['language']));
$race          = trim(mysqli_real_escape_string($conn, $_POST['race']));
$email         = trim(mysqli_real_escape_string($conn, $_POST['email']));
$c_addr        = trim(mysqli_real_escape_string($conn, $_POST['addr']));

if (!empty($child_num)) { $phone = $phone . "@$child_num"; }

if (!$customer_name || !$ic1 || !$ic2 || !$ic3 || !$phone || !$race) {
    json_err('Please fill in all required fields.');
}

// Validate IC month and day
$fragment_month = substr($ic1, 2, 2);
$fragment_day   = substr($ic1, 4, 2);
if ($fragment_month > 12 || $fragment_month == '00') { json_err('Invalid IC: month out of range.'); }
if ($fragment_day   > 31 || $fragment_day   == '00') { json_err('Invalid IC: day out of range.'); }

// Check phone duplication
$chk_phone = mysqli_query($conn, "SELECT id FROM customer WHERE phone='$phone' LIMIT 1");
if (mysqli_num_rows($chk_phone) > 0) { json_err('This phone number is already registered.'); }

// Check IC duplication
$chk_ic = mysqli_query($conn, "SELECT id FROM customer WHERE ic='$ic' LIMIT 1");
if (mysqli_num_rows($chk_ic) > 0) { json_err('This IC is already registered.'); }

// Derive gender and birth_date from IC
$gender_digit    = substr($ic3, -1);
$gender          = ($gender_digit % 2 != 0) ? 'Male' : 'Female';
$birth_year      = substr($ic, 0, 2);
$birth_month     = substr($ic, 2, 2);
$birth_day       = substr($ic, 4, 2);
$birth_year_full = ($birth_year <= 30) ? "20$birth_year" : "19$birth_year";
$birth_date      = "$birth_year_full-$birth_month-$birth_day";

$query = "INSERT INTO customer (id, c_id, date, customer_name, ic, gender, birth_date, allergic, diagnosis, language, phone, email, c_addr, operator, race, recycle)
          VALUES (NULL, '', NOW(), '$customer_name', '$ic', '$gender', '$birth_date', '', '', '$language', '$phone', '$email', '$c_addr', '$id_user', '$race', '0')";
$result = mysqli_query($conn, $query);

if (!$result) { json_err('Database error: ' . mysqli_error($conn)); }

$phone_display = preg_replace('/@.*$/', '', $phone);

echo json_encode(array(
    'success' => true,
    'ic'      => $ic,
    'name'    => $customer_name,
    'phone'   => $phone_display
));

mysqli_close($conn);
?>
