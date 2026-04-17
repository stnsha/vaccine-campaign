<?php
ob_start();
require_once('../lock_adv.php');
$connect=1;
include('../common/index_adv.php');
ob_end_clean();
header('Content-Type: application/json');

$action      = trim(mysqli_real_escape_string($conn, $_POST['action']));
$campaign_id = trim(mysqli_real_escape_string($conn, $_POST['campaign_id']));

// Load campaign for permission check
$q = "SELECT type, outlets, status FROM vaccine_campaign WHERE id='$campaign_id'";
$r = mysqli_query($conn, $q);
if(!$r || !($c = $r->fetch_assoc())) {
    echo json_encode(array('success'=>false, 'message'=>'Campaign not found.'));
    mysqli_close($conn);
    exit;
}

$camp_type      = $c['type'];
$camp_outlet    = $c['outlets'];
$current_status = $c['status'];

$user_outlets    = explode(',', $outlet);
$user_has_access = ($vaccine_autho == '1') || in_array($camp_outlet, $user_outlets);

if($action == 'update_status') {
    $new_status = trim(mysqli_real_escape_string($conn, $_POST['status']));

    $can_update = false;
    if($camp_type == '1') {
        if($vaccine_autho == '1') {
            $can_update = true; // HQ can change any status on HQ campaigns
        } else if($user_has_access && $new_status == '1' && $current_status == '0') {
            $can_update = true; // Outlet can only acknowledge (0 -> 1)
        }
    } else {
        if($user_has_access) {
            $can_update = true; // Outlet or HQ can update outlet-initiated campaigns
        }
    }

    if(!$can_update) {
        echo json_encode(array('success'=>false, 'message'=>'Permission denied.'));
        mysqli_close($conn);
        exit;
    }

    $q2 = "UPDATE vaccine_campaign SET `status`='$new_status' WHERE id='$campaign_id'";
    if(mysqli_query($conn, $q2)) {
        echo json_encode(array('success'=>true));
    } else {
        echo json_encode(array('success'=>false, 'message'=>mysqli_error($conn)));
    }
} else {
    echo json_encode(array('success'=>false, 'message'=>'Unknown action.'));
}

mysqli_close($conn);
exit;
?>
