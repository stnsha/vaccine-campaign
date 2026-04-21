<?php
ob_start();
require_once('../lock_adv.php');
$connect=1;
include('../common/index_adv.php');
ob_end_clean();
header('Content-Type: application/json');

$outlet_id = trim(mysqli_real_escape_string($conn, $_GET['outlet_id']));
$v_date    = trim(mysqli_real_escape_string($conn, $_GET['v_date']));
$clinic_id = isset($_GET['clinic_id']) ? (int)$_GET['clinic_id'] : 0;

if(!$outlet_id || !$v_date) {
    echo json_encode(array('found' => false));
    mysqli_close($conn); exit;
}

if ($clinic_id > 0) {
    // Clinic already selected: exact match on outlet + date + clinic
    $q = "SELECT vc.id, vc.v_date, vcl.id as clinic_id, vcl.name, vcl.dr_name
          FROM vaccine_campaign vc
          LEFT JOIN gp_clinics vcl ON vc.clinic = vcl.id
          WHERE vc.outlets='$outlet_id' AND vc.v_date='$v_date' AND vc.clinic='$clinic_id'
          LIMIT 1";
    $r   = mysqli_query($conn, $q);
    $row = $r ? mysqli_fetch_assoc($r) : null;
    if ($row) {
        echo json_encode(array(
            'found'     => true,
            'id'        => $row['id'],
            'v_date'    => $row['v_date'],
            'clinic_id' => $row['clinic_id'],
            'clinic'    => $row['name'],
            'dr_name'   => $row['dr_name']
        ));
    } else {
        echo json_encode(array('found' => false, 'multiple' => false));
    }
} else {
    // No clinic selected: check how many campaigns exist for this outlet+date
    $q    = "SELECT vc.id, vc.v_date, vcl.id as clinic_id, vcl.name, vcl.dr_name
             FROM vaccine_campaign vc
             LEFT JOIN gp_clinics vcl ON vc.clinic = vcl.id
             WHERE vc.outlets='$outlet_id' AND vc.v_date='$v_date'";
    $r    = mysqli_query($conn, $q);
    $rows = array();
    if ($r) { while ($row = mysqli_fetch_assoc($r)) { $rows[] = $row; } }
    $count = count($rows);
    if ($count == 1) {
        echo json_encode(array(
            'found'     => true,
            'id'        => $rows[0]['id'],
            'v_date'    => $rows[0]['v_date'],
            'clinic_id' => $rows[0]['clinic_id'],
            'clinic'    => $rows[0]['name'],
            'dr_name'   => $rows[0]['dr_name']
        ));
    } else if ($count > 1) {
        echo json_encode(array('found' => false, 'multiple' => true, 'count' => $count));
    } else {
        echo json_encode(array('found' => false, 'multiple' => false));
    }
}

mysqli_close($conn); exit;
?>
