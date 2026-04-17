<?php
ob_start();
require_once('../lock_adv.php');
$connect=1;
include('../common/index_adv.php');
ob_end_clean();

$q = trim(mysqli_real_escape_string($conn, $_GET['q']));
if(!$q) exit;

$outlet_filter = ($vaccine_autho != '1' && $outlet) ? "AND id IN ($outlet)" : '';
$sql = "SELECT id, code FROM outlet WHERE recycle='0'
        AND code NOT LIKE 'NEC%' AND code NOT LIKE 'NHQ%'
        AND code NOT LIKE '%0'   AND code NOT LIKE '%C'
        AND code NOT LIKE '%HQ'  AND code NOT LIKE 'NSDW%'
        AND code LIKE '%$q%'
        $outlet_filter
        ORDER BY code";
$rsd = mysqli_query($conn, $sql);
while($rs = mysqli_fetch_array($rsd)) {
    echo $rs['id'] . " : " . $rs['code'] . "\n";
}
?>
