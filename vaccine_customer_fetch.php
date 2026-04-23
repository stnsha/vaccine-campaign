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

$ic = trim(mysqli_real_escape_string($conn, $_GET['ic']));
if (!$ic) { json_err('No IC provided.'); }

$query = "SELECT id, customer_name, ic, phone, language, race, nationality, email, c_addr
          FROM customer WHERE ic='$ic' AND recycle=0 LIMIT 1";
$result = mysqli_query($conn, $query);
$row    = $result ? mysqli_fetch_assoc($result) : null;

if (!$row) { json_err('Customer not found.'); }

$phone_str   = stripslashes($row['phone']);
$phone_parts = explode('@', $phone_str);

echo json_encode(array(
    'success'     => true,
    'id'          => $row['id'],
    'name'        => stripslashes($row['customer_name']),
    'ic'          => stripslashes($row['ic']),
    'phone'       => $phone_parts[0],
    'child_num'   => isset($phone_parts[1]) ? $phone_parts[1] : '',
    'language'    => stripslashes($row['language']),
    'race'        => stripslashes($row['race']),
    'nationality' => stripslashes($row['nationality']),
    'email'       => stripslashes($row['email']),
    'addr'        => stripslashes($row['c_addr'])
));

mysqli_close($conn);
?>
