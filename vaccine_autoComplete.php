<?php
$connect=1;
include ('../common/index_adv.php');
$q=trim(mysqli_real_escape_string($conn,$_GET["q"]));

if (!$q) return;

$sql = "SELECT `id`, `clinic`, `dr_name` FROM `vaccine_clinic` where recycle=0 and (clinic LIKE '%$q%' or dr_name LIKE '%$q%')";
$rsd = mysqli_query($conn,$sql);
while($rs = mysqli_fetch_array($rsd)) {
	$id = $rs['id'];
	$clinic = $rs['clinic'];
	$dr_name = $rs['dr_name'];
	echo "$id : $clinic ($dr_name)\n";
}
?>