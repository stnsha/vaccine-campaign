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
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');

//search for vaccine type
$vaccine_arr=array();
$query6="select * from `vaccine_type` where recycle=0";
$result6=mysqli_query($conn, $query6);
$num6 = mysqli_num_rows ($result6);
if ($num6 > 0 ) {
$i6=0;
while ($row6 = $result6->fetch_assoc()) {
$id = stripslashes($row6['id']);
$vaccine_name = stripslashes($row6['vaccine_name']);
$vaccine_arr[$id]=$vaccine_name;
$i6++;
}}

//delete
if(isset($_GET['d'])){
	$del_id = trim(mysqli_real_escape_string($conn, $_GET['id']));
	$query2="delete from `vaccine_code` where `id`='$del_id'";
	$result2=mysqli_query($conn, $query2);
	echo "<div  style='background-color: #04ef62;'>Vaccine code DELETED! </div>";
}

//search
$option='';
if(isset($_REQUEST['s'])){
$option1='';
$option2='';
$key = trim(mysqli_real_escape_string($conn, $_REQUEST['key']));
$selected_vaccine_type = trim(mysqli_real_escape_string($conn, $_REQUEST['vaccine_type']));
if($key){
	$option1="and (`vaccine_code`.`item_code` like '$key%' or `name` like '$key%')";
	}
if($selected_vaccine_type){
	$option2="and `vaccine_type`='$selected_vaccine_type'";
	}
$option="$option1 $option2";
}

//get page no
if (isset($_REQUEST['pageno'])) {
   $pageno = $_REQUEST['pageno'];
} else {
   $pageno = 1;
}

//Search total page no
$query = "select count(*) from vaccine_code left join simple on vaccine_code.item_code=simple.item_code where 1=1 $option order by `vaccine_type`, name ";
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

$query="select `vaccine_code`.`id` as `id`, `vaccine_code`.`item_code`, `vaccine_type`, `name` from vaccine_code left join simple on vaccine_code.item_code=simple.item_code where 1=1 $option order by `vaccine_type`, name $limit";
$result=mysqli_query($conn, $query);
$num = mysqli_num_rows ($result);
?>
<div class="header"><b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
             <h1 class="headerH1"><img src='../common/img/vaccine.png'> List of Vaccine Item Code(s)</h1>
             <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<div class='paginator0'><?php if($vaccine_autho=='1'){ echo "<a href='vaccine_add_code.php'> <img src='../common/img/plus.png' width='18px'> Add Vaccine</a>";} ?></div>
<?php
//display page option
if ($pageno == 1) {
   echo "<div class='paginator'><img src='../common/img/fast_back_grey.png'> <img src='../common/img/backward_grey.png'></div> ";
} else {
   echo "<div class='paginator'><a href='{$_SERVER['PHP_SELF']}?pageno=1&key=$key&vaccine_type=$selected_vaccine_type&s=1' title='First Page'><img src='../common/img/fast_back.png'></a>";
   $prevpage = $pageno-1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$prevpage&key=$key&vaccine_type=$selected_vaccine_type&s=1' title='Previous Page'><img src='../common/img/backward.png'></a></div> ";
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
<input type='hidden' name='vaccine_type' value='<?php echo "$selected_vaccine_type"; ?>' />
 </form>
 <div class='paginator2'>of <?php echo $lastpage; ?> )</div>
 <div class='paginator'>
<?php
if ($pageno == $lastpage) {
   echo " <img src='../common/img/front_grey.png'> <img src='../common/img/fast_front_grey.png'></div> ";
} else {
   $nextpage = $pageno+1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$nextpage&key=$key&vaccine_type=$selected_vaccine_type&s=1
   ' title='Following Page'><img src='../common/img/front.png'></a> ";
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$lastpage&key=$key&vaccine_type=$selected_vaccine_type&s=1' title='Last Page'><img src='../common/img/fast_front.png'></a></div> ";
}
?>
<div class='paginator3'>
<a href='vaccine_index_item.php' title='Show all'><img src='../common/img/all.png'></a>
<form id="FormName" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName" style='display: inline-block;';>
	<?php
		echo "<select name='vaccine_type' onchange='submit()' >";
		echo "<option value=''>All</option>";
		foreach ($vaccine_arr as $id => $vaccine_type) {
			if($selected_vaccine_type==$id){$s='selected';} else {$s='';}
			echo "<option value='$id' $s>$vaccine_type</option>";
		}
		echo "</select>";
	?>
	<input type="text" name="key" autocomplete="off" placeholder='Item Code/Description' autofocus />
	<input type='hidden' name='s' value='1' />
</form>
</div>
<fieldset>
	<form id="view_form" name="view_form" action="print.php" method="post" target="Map">
	<table border="0" cellpadding="4" cellspacing="1" bgcolor="#EFEFEF" width="100%" class='myTable'>
		<tr>
			<th width='5%'>Num</th>
			<th width='10%'>Type</th>
			<th width='10%'>Item Code</th>
			<th width='60%'>Description</th>
			<?php if ($vaccine_autho=='1') echo "<th width='10%'>Edit</th>"; ?>
		</tr>
<?php
if ($num > 0 ) {
$i=0;
while ($row = $result->fetch_assoc()) {
$vaccine_id = stripslashes($row['id']);
$vaccine_type = stripslashes($row['vaccine_type']);
$item_code = stripslashes($row['item_code']);
$description = stripslashes($row['name']);

//calculate numbering asc
$r=(($pageno-1)*$rows_per_page)+$i+1;
?>
	<tr>
		<td align='center'><?php echo $r; ?>.</td>
		<td align='center'><?php echo $vaccine_arr[$vaccine_type]; ?></td>
		<td align='center'><?php echo $item_code; ?></td>
		<td>
			<?php
				echo $description;
			?>
		</td>
		<?php if ($vaccine_autho=="1") { ?>
		<td align = 'center'><?php echo "<a href=\"vaccine_update_code.php?id=$vaccine_id\" title = 'Update'><img src='../common/img/edit.png' width='16px'></a> - <a href=\"vaccine_index_item.php?id=$vaccine_id&d=1\" title = 'Delete' onclick='return confirm(\"Are you sure to remove this vaccine code?\")'><img src='../common/img/trash.png' width='16px'></a>" ?></td>
		<?php } ?>
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
