<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');

$v_date=trim(mysqli_real_escape_string($conn,$_POST['v_date']));
$outlet=$_POST['outlet'];
$clinic_id=$_POST['clinic'];

// HQ-initiated: status 0 (waiting for outlet ack). Outlet-initiated: status 1 (auto-acknowledged).
if($vaccine_autho=='1') {
	$type = '1';
	$initial_status = '0';
} else {
	$type = '2';
	$initial_status = '1';
}

$last_id = 0;

//loop to insert
$total=count($outlet);
for ($i = 0; $i < $total; $i++) {
	//prevent double entry
	$query2="SELECT id from `vaccine_campaign` where `v_date`='$v_date' and `outlets`='$outlet[$i]'";
	$result2=mysqli_query($conn,$query2);
	$row2 = $result2 -> fetch_assoc();
	@$existing_id = stripslashes($row2['id']);
	if(!$existing_id){
		$query="INSERT INTO `vaccine_campaign` (`id`, `v_date`, `outlets`, `clinic`, `type`, `status`) VALUES (NULL, '$v_date', '$outlet[$i]', '$clinic_id[$i]', '$type', '$initial_status')";
		$result=mysqli_query($conn, $query);
		$last_id = mysqli_insert_id($conn);
	} else {
		$last_id = $existing_id;
	}
}
redirect("vaccine_campaign.php?id=$last_id");
?>
