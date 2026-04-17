<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');

if(isset($_POST['submit']))  {
$campaign_id=trim(mysqli_real_escape_string($conn,$_POST['id']));
$v_date=trim(mysqli_real_escape_string($conn,$_POST['v_date']));
$outlets=trim(mysqli_real_escape_string($conn,$_POST['outlets']));
$clinic=trim(mysqli_real_escape_string($conn,$_POST['clinic']));

//search if same date and same outlet, any existing campaign or not
$sql2="SELECT `id` from `vaccine_campaign` where `v_date`='$v_date' and `outlets`='$outlets' and `id`!='$campaign_id'";
$result2 = mysqli_query($conn, $sql2);
$num2=mysqli_num_rows($result2);
$row2 = $result2 -> fetch_assoc();
@$id= stripslashes($row2["id"]);
if(!$id){
	$query="update `vaccine_campaign` set `v_date`='$v_date', `outlets`='$outlets', `clinic`='$clinic' where `id`='$campaign_id'";
	$result=mysqli_query($conn, $query);
	if($result){
		redirect("vaccine_campaign.php?id=$campaign_id&updated=1");
	}
} else {
	echo "Repeated campaign found! <br><a href='vaccine_calendar.php'>Back to Calendar</a>";
}
} else {
$campaign_id=trim(mysqli_real_escape_string($conn,$_GET['id']));
$query="SELECT * FROM `vaccine_campaign` where `id`='$campaign_id'";
$result=mysqli_query($conn, $query);
$row = $result -> fetch_assoc();
@$v_date= stripslashes($row["v_date"]);
@$outlets= stripslashes($row["outlets"]);
@$clinic= stripslashes($row["clinic"]);
@$camp_type= stripslashes($row["type"]);

// Permission check: HQ edits type-1; outlet (or HQ) edits type-2
$user_outlets_edit = explode(',', $outlet);
$edit_has_access = ($vaccine_autho=='1') || in_array($outlets, $user_outlets_edit);
$edit_allowed = ($camp_type=='1' && $vaccine_autho=='1') || ($camp_type=='2' && $edit_has_access) || ($camp_type=='');
if(!$edit_allowed) {
    echo "<fieldset class='center'><img src='../common/img/warning.png'><br/>You do not have permission to edit this campaign.<br/><a href='vaccine_campaign.php?id=$campaign_id'>Back</a></fieldset>";
    $connect=0; include('../common/index_adv.php'); exit;
}

?>
		<link rel="stylesheet" href="../common/css/jquery-ui.css" type="text/css" >
		<script src="../common/js/jquery-1.5.1.js"></script>
		<script src="../common/js/jquery.ui.core.js"></script>
		<script src="../common/js/jquery.ui.datepicker.js"></script>
		<script>
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

			$(function() {
				$( "#v_date" ).datepicker({dateFormat: 'yy-mm-dd', changeMonth: true,changeYear: true});
			});
  </script>
<div class="header"><b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
             <h1 class="headerH1"><img src='../common/img/vaccine.png'> Update Campaign </h1>
             <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<fieldset>
		<form id="view_form" name="view_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<table class='myTable'>
		<tr>
			<th style="background-color:#9999ff" width = "150">
				<div align='right'>Vaccination Date<font color='red'>*</font> :</div>
			</th>
			<td>
				<input id="v_date" name="v_date" type="text" maxlength="10" value='<?php echo $v_date; ?>' onkeydown="return tabOnEnter(this,event)" size='10' autocomplete='off' required />
			</td>
		</tr>
		<tr>
			<th style="background-color:#9999ff">
				<div align='right'>Outlet<font color='red'>*</font> :</div>
			</th>
			<td>
				<div class="form-group row">
					<select name="outlets" id="outlets" autofocus required>
						<?php
							$query2="SELECT id, code FROM `outlet` where recycle=0 order by `code`";
							$result2=mysqli_query($conn,$query2);
							while ($row2 = $result2->fetch_assoc()) {
								$id = stripslashes($row2['id']);
								$code = stripslashes($row2['code']);
								if($outlets==$id){$v="selected";} else {$v="";}
								echo "<option $v value='$id'>$code</option>";
							}
						?>
					</select>
				</div>
			</td>
		</tr>
		<tr>
			<th style="background-color:#9999ff">
				<div align='right'>Clinic<font color='red'>*</font> :</div>
			</th>
			<td>
				<select name='clinic' required>
				<option value=''>Pick One</option>
				<?php
				$query4="SELECT `id`, `dr_name`, `clinic` FROM  `vaccine_clinic` where recycle=0";
				$result4 = mysqli_query($conn,$query4);
					while($nt=mysqli_fetch_array($result4)){
					if($clinic==$nt[id]){$s='selected';} else {$s='';}
					echo "<option $s value='$nt[id]'>$nt[clinic] - $nt[dr_name]</option>";
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<th style="background-color:#9999ff">
			</th>
			<td>
				<input type="submit" name="submit" id="submit" class="form-submit" value="Update"/>
				<input type='hidden' name='id' value='<?php echo $campaign_id; ?>' />
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
