<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');
if(isset($_POST['submit'])){
	$v_date = trim(mysqli_real_escape_string($conn, $_POST['v_date']));
	$v_time = trim(mysqli_real_escape_string($conn, $_POST['v_time']));
	$trans_id = trim(mysqli_real_escape_string($conn, $_POST['trans_id']));
	$outlet_id = trim(mysqli_real_escape_string($conn, $_POST['outlet_id']));
	$campaign = trim(mysqli_real_escape_string($conn, $_POST['campaign']));
	$item_code = trim(mysqli_real_escape_string($conn, $_POST['item_code']));
	$cust_ic = trim(mysqli_real_escape_string($conn, $_POST['cust_ic']));
	$customer_name = trim(mysqli_real_escape_string($conn, $_POST['customer_name']));
	$clinic = trim(mysqli_real_escape_string($conn, $_POST['clinic']));
	$clinic_part=explode(":",$clinic);
	$clinic_id=trim($clinic_part[0]);
	if((int)$clinic_id==0){echo "Please select clinic from dropdown list!"; exit;}
	$phone2 = trim(mysqli_real_escape_string($conn, $_POST['phone2']));
	$child_num = trim(mysqli_real_escape_string($conn, $_POST['child_num']));
	if(!empty($child_num)){$child_num= "@$child_num";} else {$child_num='';}
	$remark = trim(mysqli_real_escape_string($conn, $_POST['remark']));
	$phone2=preg_replace('/\D/', '', $phone2);
	$phone2="$phone2$child_num";

	//update customer details
	$sql4="update `customer` set `phone`='$phone2' where `customer_name`='$customer_name' and `ic`='$cust_ic'";
	$result4=mysqli_query($conn, $sql4);

	//search customer ID
	$sql3="select `id` from `customer` where `ic`='$cust_ic' and `customer_name`='$customer_name' and recycle=0";
	$result3 = mysqli_query($conn, $sql3);
	$num3=mysqli_num_rows($result3);
	$row3 = $result3 -> fetch_assoc();
	@$cust_id= stripslashes($row3["id"]);
	if(!$cust_id){
		echo "Invalid customer data found!<br>Please refill again customer info.<br><a href='vaccine_update.php?id=$trans_id'>Back</a>"; exit;
	}

	//prevent duplication
	$sql2="select id from `vaccine_trans` where `outlet_id`='$outlet_id' and `v_date`='$v_date' and `cust_id`='$cust_id' and `item_code`='$item_code' and `id`!='$trans_id' and recycle=0";
	$result2 = mysqli_query($conn, $sql2);
	$num=mysqli_num_rows($result2);
	$row2 = $result2 -> fetch_assoc();
	@$trans_id2= stripslashes($row2["id"]);
	$campaign_id = (int)trim(mysqli_real_escape_string($conn, $_POST['campaign_id']));
	if(!$trans_id2){
		$query="UPDATE `vaccine_trans` SET `v_date`='$v_date $v_time:00', `timestamp`=NOW(), `cust_id` = '$cust_id', `item_code` = '$item_code', `clinic`='$clinic_id', `outlet_id` = '$outlet_id', `remark` = '$remark', `operator` = '$id_user', `campaign_id` = '$campaign_id', `recycle` = '0' WHERE `vaccine_trans`.`id` = '$trans_id'";
		$result=mysqli_query($conn, $query);
		redirect("vaccine_index.php");
	} else {
		echo "Duplicated transaction found!";
	}
} else {
$trans_id = trim(mysqli_real_escape_string($conn,$_GET['id']));
$query="SELECT `v_date`, `cust_id`, `item_code`, `gp_clinics`.`name`, `gp_clinics`.`dr_name`, `gp_clinics`.`id` as `clinic_id`, `batch_num`, `expiry_date`, `remark`, `status`, `operator`, `v_date`, `outlet_id`, `gp_clinics`.`name`, `dr_name` FROM `vaccine_trans` left join gp_clinics on vaccine_trans.clinic=gp_clinics.id where `vaccine_trans`.`recycle`=0 and `vaccine_trans`.`id`='$trans_id' limit 0,1";
$result=mysqli_query($conn, $query);
$num = mysqli_num_rows ($result);
$row = $result -> fetch_assoc();
@$cust_id= stripslashes($row["cust_id"]);
//search for customer name, IC, and phone
	$query3="select `customer_name`, `ic`, `phone` from `customer` where `id`='$cust_id' limit 0,1";
	$result3 = mysqli_query($conn, $query3);
	$row3 = $result3 -> fetch_assoc();
	@$customer_name= stripslashes($row3["customer_name"]);
	@$ic= stripslashes($row3["ic"]);
	@$phone= stripslashes($row3["phone"]);
	$phone_parts = explode("@",$phone);
@$v_date= stripslashes($row["v_date"]);
$v_time=substr($v_date,11,5);
$v_date=substr($v_date, 0,10);
@$outlet_id= stripslashes($row["outlet_id"]);
// Find linked campaign
$camp_q = "SELECT id FROM vaccine_campaign WHERE outlets='$outlet_id' AND v_date='$v_date' LIMIT 1";
$camp_r = mysqli_query($conn, $camp_q);
$camp_row = $camp_r ? mysqli_fetch_assoc($camp_r) : null;
$linked_campaign_id = $camp_row ? $camp_row['id'] : '';
@$clinic_id= stripslashes($row["clinic_id"]);
@$clinic= stripslashes($row["name"]);
@$dr_name= stripslashes($row["dr_name"]);
@$selected_item_code= stripslashes($row["item_code"]);
//search for vaccine type by selected item code
	$query6="SELECT `vaccine_type` from `vaccine_code` where `item_code`='$selected_item_code'";
	$result6 = mysqli_query($conn, $query6);
	$row6 = $result6 -> fetch_assoc();
	@$vaccine_type= stripslashes($row6["vaccine_type"]);
//form item drop down
	$query2="select `item_code` from `vaccine_code` where `vaccine_type`='$vaccine_type'";
	$result2 = mysqli_query($conn, $query2);
	$num2=mysqli_num_rows($result2);
	$dropdown_item.="<select name='item_code' required onchange='setFocusToTextBox(\"vip\")'><option value=''>Pick One</option>";
	if($num2>0){
		while ($row2 = $result2->fetch_assoc()) {
			$item_code = stripslashes($row2['item_code']);
				// search for item code description
				$query3="SELECT `name` from `simple` where `item_code`='$item_code' limit 0,1";
				$result3 = mysqli_query($conn, $query3);
				$num3=mysqli_num_rows($result3);
				$row3 = $result3 -> fetch_assoc();
				@$description= stripslashes($row3["name"]);
				if($selected_item_code==$item_code){$s='selected';} else {$s='';}
			$dropdown_item.="<option value='$item_code' $s>$item_code: $description</option>";
		}
	}
	$dropdown_item.="</select>";

@$remark= stripslashes($row["remark"]);
?>
<script type="text/javascript" src="../common/js/jquery-1.5.1.js"></script>
<script type='text/javascript' src="../common/js/jquery.autocomplete.js"></script>
<link rel="stylesheet" type="text/css" href="../common/css/jquery.autocomplete.css" />
<script type="text/javascript">
//autocomplete VIP dropdown list
$().ready(function() {
	$("#vip").autocomplete("vaccine_autoComplete2.php", {
		width: 500,
		matchContains: true,
		selectFirst: false,
		delay: 10
	});
	$("#vip").result(function(event, data, formatted) {

	var vip = document.getElementById("vip").value;
	var info = vip.split(':');
	var strURL="vaccine_ajax.php?ic="+info[0]+"&name="+info[1];

		var xmlhttp = getXMLHTTP();

		if (xmlhttp) {
			xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
			var parts = xmlhttp.responseText.split('|');
			document.getElementById("vipCard").innerHTML = parts[0];
			document.getElementById("phone2").value = parts[1];
			document.getElementById("cust_ic").value = parts[2];
			document.getElementById("customer_name").value = parts[3];
			document.getElementById("child_num").value = parts[4];
			setFocusToTextBox("phone2");
			}
			}
			xmlhttp.open("GET", strURL, true);
			xmlhttp.send();
		}
	});

	$("#clinic").autocomplete("vaccine_autoComplete.php", {
		width: 500,
		matchContains: true,
		selectFirst: false,
		delay: 10
	});
	$("#clinic").result(function() {
		checkCampDate();
	});
	checkCampDate();
});

function getXMLHTTP() { //fuction to return the xml http object
		var xmlhttp;
		if (window.XMLHttpRequest)
		{// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
		}
		else
		{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}

		return xmlhttp;
		}

		function getClinic(outletId){
			var strURL="vaccine_ajax.php?id="+outletId;
			var xmlhttp = getXMLHTTP();
			if (xmlhttp) {
			xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
			var parts = xmlhttp.responseText.split('|');
			document.getElementById("v_date").innerHTML = parts[0];
			}
			}
			xmlhttp.open("GET", strURL, true);
			xmlhttp.send();
			}
		}

		function getItemCode(vType){
			var strURL="vaccine_ajax.php?vt="+vType;
				var xmlhttp = getXMLHTTP();
				if (xmlhttp) {
				xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
				var parts = xmlhttp.responseText.split('|');
				document.getElementById("item_code").innerHTML = parts[0];
				}
				}
				xmlhttp.open("GET", strURL, true);
				xmlhttp.send();
			}
		}

		function setFocusToTextBox(target){
		document.getElementById(target).focus();
	}

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

	function checkCampDate() {
		var dateEl   = document.getElementById("v_date");
		var outletEl = document.getElementById("outlet_id");
		var clinicEl = document.getElementById("clinic");
		var span     = document.getElementById("camp_status");
		var date     = dateEl   ? dateEl.value   : '';
		var outlet   = outletEl ? outletEl.value  : '';
		if (!date || !outlet) { span.innerHTML = ''; return; }
		var clinicVal = clinicEl ? clinicEl.value : '';
		var clinicId  = parseInt(clinicVal.split(':')[0].trim()) || 0;
		var url = "vaccine_ajax_check_campaign.php?outlet_id=" + encodeURIComponent(outlet) + "&v_date=" + encodeURIComponent(date);
		if (clinicId > 0) { url += "&clinic_id=" + clinicId; }
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if (xhr.readyState == 4 && xhr.status == 200) {
				try {
					var r = JSON.parse(xhr.responseText);
					if (r.found) {
						document.getElementById("camp_id").value = r.id;
						span.innerHTML = '<a href="vaccine_campaign.php?id=' + r.id + '" target="_blank" style="color:green;">Campaign found: ' + r.v_date + '</a>';
					} else if (r.multiple) {
						span.innerHTML = '<font color="orange">' + r.count + ' campaigns on this date. Select a clinic first.</font>';
					} else {
						document.getElementById("camp_id").value = '';
						if (clinicId > 0) {
							span.innerHTML = '<font color="red">No campaign found.</font> <a href="javascript:void(0)" onclick="createCampaign()" style="color:#e8a800;cursor:pointer;">Create?</a>';
						} else {
							span.innerHTML = '<font color="red">No campaign found. Select clinic first to create.</font>';
						}
					}
				} catch(e) {}
			}
		};
		xhr.open("GET", url, true);
		xhr.send();
	}

	function createCampaign() {
		var date      = document.getElementById("v_date").value;
		var outlet    = document.getElementById("outlet_id").value;
		var clinicVal = document.getElementById("clinic").value;
		var clinicId  = clinicVal.split(':')[0].trim();
		var span      = document.getElementById("camp_status");
		if (!clinicId || parseInt(clinicId) == 0) {
			span.innerHTML = '<font color="red">Please select a clinic first.</font>';
			return;
		}
		span.innerHTML = 'Creating...';
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if (xhr.readyState == 4 && xhr.status == 200) {
				try {
					var r = JSON.parse(xhr.responseText);
					if (r.success) {
						document.getElementById("camp_id").value = r.id;
						span.innerHTML = '<a href="vaccine_campaign.php?id=' + r.id + '" target="_blank" style="color:green;">Campaign created</a>';
					} else {
						span.innerHTML = '<font color="red">' + r.error + '</font>';
					}
				} catch(e) { span.innerHTML = '<font color="red">Unexpected error.</font>'; }
			}
		};
		xhr.open("POST", "vaccine_campaign_save.php", true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.send("outlet_id=" + encodeURIComponent(outlet) + "&v_date=" + encodeURIComponent(date) + "&clinic_id=" + encodeURIComponent(clinicId));
	}
</script>
<div class="header"><b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
             <h1 class="headerH1"><img src='../common/img/vaccine.png' height='18px'> Add New Transaction</h1>
             <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<fieldset>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="form1">
		<table border="0" cellpadding="4" cellspacing="1" bgcolor="#EFEFEF" width="100%" class='myTable'>
			<tr>
				<th style="text-align: right;">Outlet <span style="color: red;">*</span></th>
				<td colspan='3'>
				<?php
				$query4="SELECT outlet FROM  `staff` where id=$id_user";
				$result4 = mysqli_query($conn,$query4);
				$row4 = $result4 -> fetch_assoc();
				@$outlet = stripslashes($row4['outlet']);

				if($vaccine_autho=='1'){
				$query3="select `id`, `code` from outlet where recycle='0' and `code` NOT LIKE 'NEC%' AND `code` NOT LIKE 'NHQ%' AND `code` NOT LIKE '%0' AND `code` NOT LIKE '%C' AND `code` NOT LIKE '%HQ' AND `code` NOT LIKE 'NSDW%' order by `code`";
				$result3=mysqli_query($conn,$query3);
				$num3 = mysqli_num_rows ($result3);

				if ($num3 > 0 ) {
				$i3=0;
				$outlet='';
				while ($row3 = $result3->fetch_assoc()) {
				$id = stripslashes($row3['id']);
				if($i3=='0'){
				$outlet.="$id";} else {$outlet.=",$id";}
				++$i3; } }
				}
				$e_outlet = explode(",", $outlet);

					echo "<select id='outlet_id' name='outlet_id' onchange='getClinic(this.value); checkCampDate();' required>";
					echo "<option value=''>Pick One</option>";
					foreach ($e_outlet as $value) {
					$query2="SELECT code FROM `outlet` where id='$value' limit 0,1";
					$result2=mysqli_query($conn,$query2);
					$row2 = $result2 -> fetch_assoc();
					@$code = stripslashes($row2['code']);
					if($value==$outlet_id){$s='selected';} else {$s='';}
					echo "<option value='$value' $s>$code</option>";
					}
					echo "</select>";
				?>
				</td>
			</tr>
			<tr>
				<th style="text-align: right;">Vaccination Date <span style="color: red;">*</span></th>
				<td colspan='3'>
					<input type='date' name='v_date' id='v_date' onkeydown="return tabOnEnter(this,event)" onchange="checkCampDate()" required value='<?php echo $v_date; ?>' />
					<input type='time' name='v_time' id='v_time' onkeydown="return tabOnEnter(this,event)" required value='<?php echo $v_time; ?>' />
				</td>
			</tr>
			<tr>
				<th style="text-align: right;">Campaign</th>
				<td colspan='3'>
					<span id="camp_status"></span>
					<input type="hidden" id="camp_id" name="campaign_id" value="<?php echo $linked_campaign_id; ?>" />
				</td>
			</tr>
			<tr>
				<th style="text-align: right;">Clinic <span style="color: red;">*</span></th>
				<td colspan='3'>
					<input id='clinic' name='clinic' placeholder='Clinic Name' size='50' autocomplete="off" value='<?php echo "$clinic_id: $clinic ($dr_name)"; ?>' required autofocus onkeydown="return tabOnEnter(this,event)" />
				</td>
			</tr>
			<tr>
				<th style="text-align: right;">Vaccine Type <span style="color: red;">*</span></th>
				<td colspan='3'>
					<select name='vaccine_type' onchange='getItemCode(this.value);' required>
						<option value=''>Pick One</option>
						<?php

							$query5="SELECT `id`, `vaccine_name` from `vaccine_type` where recycle=0";
							$result5 = mysqli_query($conn, $query5);
							while ($row5 = $result5->fetch_assoc()) {
								$type_id = stripslashes($row5['id']);
								$vaccine_name = stripslashes($row5['vaccine_name']);
								if($type_id==$vaccine_type){$s='selected';} else {$s='';}
								echo "<option value='$type_id' $s>$vaccine_name</option>";
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th style="text-align: right;">Item Code <span style="color: red;">*</span></th>
				<td colspan='3'><span id='item_code'><?php echo $dropdown_item; ?></span></td>
			</tr>
			<tr>
				<th style="text-align: right;">Customer Name <span style="color: red;">*</span></th>
				<td>
					<input id='vip' name='vip' placeholder='IC,VIP ID,Name' size='50' autocomplete="off" value='<?php if($ic!=""){echo "$ic : $customer_name";} ?>'required autofocus onkeydown="return tabOnEnter(this,event)" />
					<span id='vipCard'></span>
					<input id='cust_ic' name='cust_ic' type='hidden' value='<?php echo $ic; ?>' />
					<input id='customer_name' name='customer_name' type='hidden' value='<?php echo $customer_name; ?>' />
				</td>
				<th width='150px' style="text-align: right;">Customer's <br/>Contact <span style="color: red;">*</span></td>
				<td width='30%'>
					<input id='phone2' name='phone2' placeholder="Phone Num" size='15' autocomplete="off" onkeydown="return tabOnEnter(this,event)" required value='<?php echo $phone_parts[0]; ?>' />@
					<select name='child_num' id='child_num'>
						<option></option>
						<?php
						for ($x = 1; $x <= 10; $x++) {
							if($phone_parts[1]==$x){$s='selected';} else {$s='';}
							echo "<option $s>$x</option>";
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th style="text-align: right;">Remark(s)</th>
				<td colspan='3'>
					<textarea name='remark' rows='3' cols='50'><?php echo $remark; ?></textarea>
				</td>
			</tr>
			<tr>
				<th></th>
				<td colspan='3'>
					<input type="submit" name="submit" value="Update" />
					<input type='hidden' name='trans_id' value='<?php echo $trans_id; ?>' />
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
