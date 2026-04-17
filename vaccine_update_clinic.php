<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');
if(isset($_POST['submit'])){
	$clinic_id=trim(mysqli_real_escape_string($conn,$_POST['clinic_id']));
	$clinic=trim(mysqli_real_escape_string($conn,$_POST['clinic']));
	$clinic=ucwords(strtolower($clinic));
	$c_phone=trim(mysqli_real_escape_string($conn,$_POST['c_phone']));
	$dr_name=trim(mysqli_real_escape_string($conn,$_POST['dr_name']));
	$dr_name=ucwords(strtolower($dr_name));
	$address=trim(mysqli_real_escape_string($conn,$_POST['address']));
	//avoid duplicate
	$query2="SELECT count(id) as count FROM `vaccine_clinic` where `clinic`='$clinic' and `c_phone`='$c_phone' and id!='$clinic_id' and recycle=0 limit 0,1";
	$result2=mysqli_query($conn,$query2);
	$row2 = $result2 -> fetch_assoc();
	@$count = stripslashes($row2['count']);

	if($count==0){
		$query3="UPDATE `vaccine_clinic` SET `clinic` = '$clinic', `c_phone` = '$c_phone', `dr_name` = '$dr_name', `address` = '$address' WHERE `vaccine_clinic`.`id` = $clinic_id;";
		echo $query3;
		$result3=mysqli_query($conn, $query3);
		if($result3){
			redirect("vaccine_index_clinic.php?s=1&id=$clinic_id");
		}
	} else {
		echo "Duplicated account found!";
	}
} else {
$clinic_id = trim(mysqli_real_escape_string($conn, $_GET['id']));
$query="SELECT * FROM  `vaccine_clinic` WHERE `id`='$clinic_id'";
$result=mysqli_query($conn, $query);
$row = $result -> fetch_assoc();
@$clinic= stripslashes($row["clinic"]);
@$dr_name= stripslashes($row["dr_name"]);
@$c_phone= stripslashes($row["c_phone"]);
@$address= stripslashes($row["address"]);
?>
<script type="text/javascript">
	function getNextElement(field) {
		var form = field.form;
		for ( var e = 0; e < form.elements.length; e++) {
			if (field == form.elements[e]) {
				break;
			}
		}
		e++;
		while (form.elements[e % form.elements.length].type == "hidden") {
		 e++;
		}
		return form.elements[e % form.elements.length];
	}

	function tabOnEnter(field, evt) {
	if (evt.keyCode === 13) {
			if (evt.preventDefault) {
				evt.preventDefault();
			} else if (evt.stopPropagation) {
				evt.stopPropagation();
			} else {
				evt.returnValue = false;
			}
			getNextElement(field).focus();
			return false;
		} else {
			return true;
		}
	}
</script>
<div class="header"><b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
             <h1 class="headerH1"><img src='../common/img/vaccine.png' height='18px'> Update Clinic</h1>
             <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<fieldset>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="form1">
		<table border="0" cellpadding="4" cellspacing="1" width="100%" class='myTable'>
			<tr>
				<th width='150px' align='right'><b>Clinic Name : </b></th>
				<td>
					<input id='clinic' name='clinic' value='<?php echo $clinic; ?>' required autofocus onkeydown="return tabOnEnter(this,event)" maxlength='100' />
				</td>
			</tr>
			<tr>
				<th align='right'><b>Clinic's <br/>Contact : </b></th>
				<td>
					<input id='c_phone' name='c_phone' placeholder="Phone Num" size='15' value='<?php echo $c_phone; ?>' autocomplete="off" onkeydown="return tabOnEnter(this,event)" required />
				</td>
			</tr>
			<tr>
				<th align='right'><b>Doctor In Charge : </b></th>
				<td colspan='3'>
					<input type='text' name='dr_name' value='<?php echo $dr_name; ?>' onkeydown="return tabOnEnter(this,event)" required maxlength='50' />
				</td>
			</tr>
			<tr>
				<th align='right'><b>Address : </b></th>
				<td colspan='3'>
					<textarea name='address' rows='3' cols='50'><?php echo $address; ?></textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td colspan='3'>
					<input type="submit" name="submit" value="Update" />
					<input type='hidden' name='clinic_id' value='<?php echo $clinic_id; ?>' />
				</td>
			</tr>
	</table>
	</form>
</fieldset>
<?php
}
$connect=0;
include ('../common/index_adv.php');
?>
