<?php
$connect=1;
include ('../common/index_adv.php');
$q=trim(mysqli_real_escape_string($conn,$_GET["q"]));

if (!$q) return;

$sql = "SELECT `id`, `name`, `dr_name` FROM `gp_clinics` where is_active=1 and (name LIKE '%$q%' or dr_name LIKE '%$q%')";
$rsd = mysqli_query($conn,$sql);
while($rs = mysqli_fetch_array($rsd)) {
	$id = $rs['id'];
	$clinic = $rs['name'];
	$dr_name = $rs['dr_name'];
	echo "$id : $clinic ($dr_name)\n";
}
?>