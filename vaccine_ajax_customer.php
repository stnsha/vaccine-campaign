<?php
ob_start();
$connect=1;
include('../common/index_adv.php');
ob_clean();
header('Content-Type: application/json');

$ic = trim(mysqli_real_escape_string($conn, isset($_GET['ic']) ? $_GET['ic'] : ''));
if(!$ic){
    echo json_encode(array('found' => false, 'msg' => 'IC required'));
    mysqli_close($conn);
    exit;
}

$q = "SELECT id, customer_name, phone, email, c_addr, language, race, nationality, diagnosis, allergic FROM customer WHERE ic='$ic' AND recycle=0 LIMIT 1";
$r = mysqli_query($conn, $q);
if($r && mysqli_num_rows($r) > 0){
    $c = mysqli_fetch_assoc($r);
    $phone_parts    = explode('@', $c['phone']);
    $c['phone_num'] = preg_replace('/\D/', '', $phone_parts[0]);
    $c['child_num'] = isset($phone_parts[1]) ? $phone_parts[1] : '';
    unset($c['phone']);
    echo json_encode(array('found' => true, 'data' => $c));
} else {
    echo json_encode(array('found' => false, 'msg' => 'Customer not found'));
}
mysqli_close($conn);
?>
