<?php
$connect=1;
include ('../common/index_adv.php');
$q=trim(mysqli_real_escape_string($conn,$_GET['q'] ?? ''));

if (!$q) return;

// IC / customer ID input: prefix match so the `ic` index is used (full LIKE '%q%' scans the whole table)
if (preg_match('/^[0-9A-Za-z-]+$/', $q) && preg_match('/[0-9]/', $q)) {
	$sql = "select customer_name, ic from customer where ic LIKE '$q%' and recycle=0 order by ic limit 20";
	$rsd = mysqli_query($conn,$sql);
	if ($rsd && mysqli_num_rows($rsd) == 0 && strlen($q) > 12) {
		$sql = "select customer_name, ic from customer where c_id LIKE '$q%' and recycle=0 limit 20";
		$rsd = mysqli_query($conn,$sql);
	}
} else {
	if (strlen($q) < 3) return;
	$sql = "select customer_name, ic from customer where customer_name LIKE '%$q%' and recycle=0 limit 20";
	$rsd = mysqli_query($conn,$sql);
}

if ($rsd) {
	while($rs = mysqli_fetch_array($rsd)) {
		$ic = $rs['ic'];
		$cname = $rs['customer_name'];
		echo "$ic : $cname\n";
	}
}
?>
