<?php
$connect=1;
include ('../common/index_adv.php');
$q=trim(mysqli_real_escape_string($conn,$_GET["q"]));
if (!$q) return;

$sql = "select DISTINCT name as description, item_code from simple where (`name` LIKE '%$q%' or `item_code` LIKE '%$q%') and `item_code` REGEXP '^[0-9]+$'";
$rsd = mysqli_query($conn,$sql);
$num = mysqli_num_rows ($rsd);
while($rs = mysqli_fetch_array($rsd)) {
	$cname = $rs['description'];
	$cname = str_replace("'", "", $cname);
	$code = $rs['item_code'];
	echo "$code : $cname\n";
}
?>
