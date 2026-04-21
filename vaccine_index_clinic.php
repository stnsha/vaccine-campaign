<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<link rel="stylesheet" media="screen" type="text/css" href="../common/css/layout.css" />
<style>
		.paginator0{
		display:inline;
		position: absolute;
		left: 6%
		}

		.paginator{
		display:inline;
		position:relative;
		left:0%;
		}

		.paginator2{
		display:inline;
		position:relative;
		top:-5px;
		left:0%;
		}

		.paginator3{
		display:inline;
		position:absolute;
		right:6%;
		}
</style>
<?php
require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');

//search latest date by clinic from vaccine_trans
$query3="SELECT clinic, MAX(v_date) AS latest_date FROM vaccine_trans GROUP BY clinic";
$result3=mysqli_query($conn, $query3);
$num3 = mysqli_num_rows ($result3);
$clinic_arr=array();
if ($num3 > 0 ) {
while ($row3 = $result3->fetch_assoc()) {
$clinic_id = stripslashes($row3['clinic']);
$latest_date = stripslashes($row3['latest_date']);
$clinic_arr[$clinic_id]=$latest_date;
}}

//reactivate
if(isset($_GET['r'])){
	$reactivate_id = trim(mysqli_real_escape_string($conn, $_GET['id']));
	$query2="update `gp_clinics` set `is_active`=1 where `id`='$reactivate_id'";
	$result2=mysqli_query($conn, $query2);
}

//delete
if(isset($_GET['d'])){
	$del_id = trim(mysqli_real_escape_string($conn, $_GET['id']));
	$query2="update `gp_clinics` set `is_active`=0 where `id`='$del_id'";
	$result2=mysqli_query($conn, $query2);
}

//search
$option='';
if(isset($_REQUEST['s'])){
$key = trim(mysqli_real_escape_string($conn, $_REQUEST['key']));
$clinic_id = trim(mysqli_real_escape_string($conn, $_GET['id']));
if($clinic_id){
	$option="and `id`='$clinic_id'";
} else
if($key){
	$option="and (`name` like '%$key%' or `address` like '%$key%' or `dr_name` like '%$key%')";
	}
}

//get page no
if (isset($_REQUEST['pageno'])) {
   $pageno = $_REQUEST['pageno'];
} else {
   $pageno = 1;
}

//Search total page no
$query = "SELECT count(*) FROM  `gp_clinics` WHERE `is_active`='1' $option";
$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
$num_rows = mysqli_fetch_row($result);
$numrows = $num_rows[0];

//page setting
$lastpage      = ceil($numrows/$rows_per_page);

//ensure pageno within range
$pageno = (int)$pageno;
if ($pageno > $lastpage) {
   $pageno = $lastpage;
} // if
if ($pageno < 1) {
   $pageno = 1;
} // if

//limit
$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;

$query="SELECT * FROM  `gp_clinics` WHERE `is_active`='1' $option order by `name` $limit";
$result=mysqli_query($conn, $query);
$num = mysqli_num_rows ($result);
?>
<div class="header"><b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
             <h1 class="headerH1"><img src='../common/img/vaccine.png'> List of Clinics (Active)</h1>
             <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<div class='paginator0'><?php $option=urlencode($option); if (strpos(strtolower($status_semasa), 'pharmacist') !== false || $status_semasa=='Assistant Branch Manager' || $status_semasa=='Branch Manager' || $status_semasa=='Associate Area Manager' || $status_semasa=='Area Manager' || $vaccine_autho==1) { echo "<a href='vaccine_add_clinic.php'> <img src='../common/img/plus.png' width='18px'></a> | <img src='../common/img/star.png'>Active | <font size='1'><a href='vaccine_deactivated_clinic.php'><img src='../common/img/star.png' width='18px' style='filter: grayscale(100%);'>Deactivated</a></font>"; echo "| <a href='vaccine_export.php' title='Download List'><img src='../common/img/download.png'></a>";} ?></div>
<?php
//display page option
if ($pageno == 1) {
   echo "<div class='paginator'><img src='../common/img/fast_back_grey.png'> <img src='../common/img/backward_grey.png'></div> ";
} else {
   echo "<div class='paginator'><a href='{$_SERVER['PHP_SELF']}?pageno=1&outlet_id=$outlet_id&s=1&depart_selected=$depart_selected&key=$key' title='First Page'><img src='../common/img/fast_back.png'></a>";
   $prevpage = $pageno-1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$prevpage&outlet_id=$outlet_id&s=1&depart_selected=$depart_selected&key=$key' title='Previous Page'><img src='../common/img/backward.png'></a></div> ";
}
?>
 <div class='paginator2'>( Page </div>
 <form  class='paginator' method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>"> <select name="pageno" onchange="submit()">
 <?php for($i=1;$i<=$lastpage;$i++){
  $select='';
 if($pageno==$i){$select='selected';}
 echo "<option value=$i $select>$i</option>";
}
 ?>
 </select>
<input type='hidden' name='key' value='<?php echo "$key"; ?>' />
<input type='hidden' name='s' value='1' />
 </form>
 <div class='paginator2'>of <?php echo $lastpage; ?> )</div>
 <div class='paginator'>
<?php
if ($pageno == $lastpage) {
   echo " <img src='../common/img/front_grey.png'> <img src='../common/img/fast_front_grey.png'></div> ";
} else {
   $nextpage = $pageno+1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$nextpage&outlet_id=$outlet_id&s=1&depart_selected=$depart_selected&key=$key
   ' title='Following Page'><img src='../common/img/front.png'></a> ";
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$lastpage&outlet_id=$outlet_id&s=1&depart_selected=$depart_selected&key=$key' title='Last Page'><img src='../common/img/fast_front.png'></a></div> ";
}
?>
<div class='paginator3'>
<a href='vaccine_index_clinic.php' title='Show all'><img src='../common/img/all.png'></a>
<form id="FormName" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName" style='display: inline-block;';>
	<input type="text" name="key" autocomplete="off" placeholder='Clinic Name' autofocus />
	<input type='hidden' name='s' value='1' />
</form>
</div>
<fieldset>
	<form id="view_form" name="view_form" action="print.php" method="post" target="Map">
	<table border="0" cellpadding="4" cellspacing="1" bgcolor="#EFEFEF" width="100%" class='myTable'>
		<tr>
			<th width='3%'>Num</th>
			<th width='20%'>Clinic Name</th>
			<th width='10%'>Contact</th>
			<th width='15%'>In Charge</th>
			<th width='35%'>Address</th>
			<th width='10%'>Latest Event</th>
			<?php if (strpos(strtolower($status_semasa), 'pharmacist') !== false || $status_semasa=='Assistant Branch Manager' || $status_semasa=='Branch Manager' || $status_semasa=='Associate Area Manager' || $status_semasa=='Area Manager' || $vaccine_autho==1) { ?><th width='7%'>Edit</th><?php } ?>
		</tr>
<?php
$sixMonthsAgo = (new DateTime())->modify('-6 months');
if ($num > 0 ) {
$i=0;
while ($row = $result->fetch_assoc()) {
$clinic_id = stripslashes($row['id']);
$clinic = stripslashes($row['name']);
$c_phone = stripslashes($row['phone_1']);
$dr_name = stripslashes($row['dr_name']);
$address = stripslashes($row['address']);

if (isset($clinic_arr[$clinic_id]) && $clinic_arr[$clinic_id] && new DateTime($clinic_arr[$clinic_id]) < $sixMonthsAgo) {
	$color='red';
} else {$color='black';}

$hp=preg_replace('/\D/', '', $c_phone);
	$prefix='60';
	$prefix2='6';
	if(substr("$hp", 0, 2)=='01'){$new="<a href='javascript:void(0);' onclick=\"window.open(&quot;https://api.whatsapp.com/send?phone=$prefix2$hp&text=Dear%20$dr_name,%0A%0A&language=en&quot;,&quot;Ratting&quot;,&quot;width=1,height=1,left=1,top=1,toolbar=no,scrollbars=no,menubar=no,resizable=no&quot;);\" title='Send Whatsapp'><img src='../common/img/wa.png' width='15px'>$hp</a><br/>";} else if(substr("$hp", 0, 1)=='1'){$new="<a href='javascript:void(0);' onclick=\"window.open(&quot;https://api.whatsapp.com/send?phone=$prefix2$hp&text=Dear%20$dr_name,%0A%0A&language=en&quot;,&quot;Ratting&quot;,&quot;width=1,height=1,left=1,top=1,toolbar=no,scrollbars=no,menubar=no,resizable=no&quot;);\" title='Send Whatsapp'><img src='../common/img/wa.png' width='15px'>$hp</a><br/>";} else {$new="$hp";}

//calculate numbering asc
$r=(($pageno-1)*$rows_per_page)+$i+1;
?>
	<tr>
		<td align='center'><?php echo $r; ?>.</td>
		<td><?php echo $clinic; ?></td>
		<td><?php echo $new; ?></td>
		<td><?php echo $dr_name; ?></td>
		<td align='center'><?php echo $address; ?></td>
		<td align='center'><font color='<?php echo $color; ?>'><?php echo isset($clinic_arr[$clinic_id]) ? $clinic_arr[$clinic_id] : ''; ?></font></td>
		<?php if (strpos(strtolower($status_semasa), 'pharmacist') !== false || $status_semasa=='Assistant Branch Manager' || $status_semasa=='Branch Manager' || $status_semasa=='Associate Area Manager' || $status_semasa=='Area Manager' || $vaccine_autho==1) { echo "<td align = 'center'><a href=\"vaccine_update_clinic.php?id=$clinic_id\" title = 'Update'><img src='../common/img/edit.png' width='16px'></a> - <a href=\"vaccine_index_clinic.php?id=$clinic_id&d=1\" title = 'Delete' onclick=\"return confirm('Are you sure to delete this clinic?')\" ><img src='../common/img/trash.png' width='16px'></a></td>";} ?>
	</tr>
	<?php
	$i++;
	}}
	?>
	</table>
	</form>
</fieldset>
<?php
$connect=0;
include ('../common/index_adv.php');
?>
