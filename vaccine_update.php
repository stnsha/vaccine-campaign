<?php ob_start(); ?>
<!DOCTYPE html>
<link rel="stylesheet" media="screen" type="text/css" href="../common/css/layout.css" />
<style>
.idx-panel {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
    padding: 14px 18px;
    margin: 6px 0 10px;
}

.myTable {
    font-size: 13px;
    width: 100%;
}

.myTable th {
    font-size: 13px;
    text-align: left !important;
    padding: 6px 10px;
    white-space: nowrap;
    vertical-align: middle;
    width: 160px;
}

.myTable td {
    font-size: 13px;
    text-align: left !important;
    padding: 6px 10px;
    vertical-align: middle;
}

.myTable input[type="text"],
.myTable input[type="date"],
.myTable input[type="time"],
.myTable select,
.myTable textarea {
    border-radius: 8px;
    padding: 5px 8px;
    border: 1px solid #cfcfcf;
    background: #fff;
    font-size: 12px;
    font-family: Arial, Helvetica, sans-serif;
    line-height: 1.4;
    box-sizing: border-box;
}

.myTable input[type="text"]:focus,
.myTable input[type="date"]:focus,
.myTable input[type="time"]:focus,
.myTable select:focus,
.myTable textarea:focus {
    outline: none;
    border-color: rgba(0, 91, 150, .55);
    box-shadow: 0 0 0 3px rgba(0, 91, 150, .12);
}

.upd-submit {
    display: inline-flex;
    align-items: center;
    padding: 5px 20px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #005B96;
    background: #005B96;
    color: #fff;
    font-size: 12px;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, .10);
    box-sizing: border-box;
}

.upd-submit:hover {
    background: #004d80;
    border-color: #004d80;
}
.upd-back {
    display: inline-flex;
    align-items: center;
    padding: 5px 20px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #d0d7de;
    background: #e9ecef;
    color: #111;
    font-size: 12px;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, .10);
    box-sizing: border-box;
    text-decoration: none !important;
    vertical-align: middle;
}
.upd-back:hover {
    background: #d8dde3;
    border-color: #b0b8c1;
    text-decoration: none !important;
}
</style>
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
	$item_code = trim(mysqli_real_escape_string($conn, $_POST['item_code']));
	$cust_ic = trim(mysqli_real_escape_string($conn, $_POST['cust_ic']));
	$customer_name = trim(mysqli_real_escape_string($conn, $_POST['customer_name']));
	$campaign_id = (int)trim(mysqli_real_escape_string($conn, $_POST['campaign_id']));
	$clinic_id = 0;
	if($campaign_id > 0){
		$q_cl = "SELECT clinic FROM vaccine_campaign WHERE id='$campaign_id' LIMIT 1";
		$r_cl = mysqli_query($conn, $q_cl);
		$row_cl = $r_cl ? mysqli_fetch_assoc($r_cl) : null;
		if($row_cl){ $clinic_id = (int)$row_cl['clinic']; }
	}
	$phone2        = trim(mysqli_real_escape_string($conn, $_POST['phone2']));
	$child_num     = trim(mysqli_real_escape_string($conn, $_POST['child_num']));
	$remark        = trim(mysqli_real_escape_string($conn, $_POST['remark']));
	$cust_email    = trim(mysqli_real_escape_string($conn, $_POST['cust_email']));
	$cust_addr     = trim(mysqli_real_escape_string($conn, $_POST['cust_addr']));
	$cust_language = trim(mysqli_real_escape_string($conn, $_POST['cust_language']));
	$cust_race     = trim(mysqli_real_escape_string($conn, $_POST['cust_race']));
	$cust_nationality = trim(mysqli_real_escape_string($conn, $_POST['cust_nationality']));
	$cust_diagnosis   = trim(mysqli_real_escape_string($conn, $_POST['cust_diagnosis']));
	$cust_allergic    = trim(mysqli_real_escape_string($conn, $_POST['cust_allergic']));
	if($cust_language === '' || $cust_race === '' || $cust_nationality === ''){
		echo "Please fill in Language, Race, and Nationality before updating."; exit;
	}
	if(!empty($child_num)){$child_num= "@$child_num";} else {$child_num='';}
	$phone2=preg_replace('/\D/', '', $phone2);
	$phone2="$phone2$child_num";

	//update customer details
	$sql4="UPDATE customer SET customer_name='$customer_name', phone='$phone2', email='$cust_email', c_addr='$cust_addr', language='$cust_language', race='$cust_race', nationality='$cust_nationality', diagnosis='$cust_diagnosis', allergic='$cust_allergic' WHERE ic='$cust_ic' AND recycle=0";
	$result4=mysqli_query($conn, $sql4);

	//search customer ID
	$sql3="select `id` from `customer` where `ic`='$cust_ic' and recycle=0";
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
	if(!$trans_id2){
		$query="UPDATE `vaccine_trans` SET `v_date`='$v_date $v_time:00', `timestamp`=NOW(), `cust_id` = '$cust_id', `item_code` = '$item_code', `clinic`='$clinic_id', `outlet_id` = '$outlet_id', `remark` = '$remark', `operator` = '$id_user', `campaign_id` = '$campaign_id', `recycle` = '0' WHERE `vaccine_trans`.`id` = '$trans_id'";
		$result=mysqli_query($conn, $query);
		redirect("vaccine_index.php");
	} else {
		echo "Duplicated transaction found!";
	}
} else {
$trans_id = trim(mysqli_real_escape_string($conn,$_GET['id']));
$query="SELECT `vaccine_trans`.`campaign_id`, `v_date`, `cust_id`, `item_code`, `gp_clinics`.`name`, `gp_clinics`.`dr_name`, `gp_clinics`.`id` as `clinic_id`, `batch_num`, `expiry_date`, `remark`, `status`, `operator`, `v_date`, `outlet_id`, `gp_clinics`.`name`, `dr_name` FROM `vaccine_trans` left join gp_clinics on vaccine_trans.clinic=gp_clinics.id where `vaccine_trans`.`recycle`=0 and `vaccine_trans`.`id`='$trans_id' limit 0,1";
$result=mysqli_query($conn, $query);
$num = mysqli_num_rows ($result);
$row = $result -> fetch_assoc();
@$cust_id= stripslashes($row["cust_id"]);
//search for customer name, IC, and phone
	$query3="SELECT customer_name, ic, phone, email, c_addr, language, race, nationality, diagnosis, allergic FROM customer WHERE id='$cust_id' LIMIT 0,1";
	$result3 = mysqli_query($conn, $query3);
	$row3 = $result3 -> fetch_assoc();
	@$customer_name   = stripslashes($row3["customer_name"]);
	@$ic              = stripslashes($row3["ic"]);
	@$phone           = stripslashes($row3["phone"]);
	$phone_parts      = explode("@",$phone);
	@$cust_email      = stripslashes($row3["email"]);
	@$cust_addr       = stripslashes($row3["c_addr"]);
	$cust_language    = (string)$row3["language"];
	@$cust_race       = (string)$row3["race"];
	@$cust_nationality= stripslashes($row3["nationality"]);
	@$cust_diagnosis  = stripslashes($row3["diagnosis"]);
	@$cust_allergic   = stripslashes($row3["allergic"]);
	// Race dropdown
	$race_options = '';
	$r_races = mysqli_query($conn, "SELECT id, ethnic FROM mydna_ethnicities ORDER BY ethnic");
	if($r_races){
		while($rrow = mysqli_fetch_assoc($r_races)){
			$rsel = ((string)$rrow['id'] === $cust_race) ? 'selected' : '';
			$race_options .= "<option value='".htmlspecialchars($rrow['id'])."' $rsel>".htmlspecialchars($rrow['ethnic'])."</option>";
		}
	}
	// Nationality helpers
	$std_nats    = array('MALAYSIA','SINGAPORE','INDONESIA','BRUNEI','PHILIPPINES','THAILAND');
	$is_std_nat  = ($cust_nationality === '' || in_array(strtoupper($cust_nationality), $std_nats));
	$nat_sel_val = $is_std_nat ? strtoupper($cust_nationality) : 'OTHERS';
	$nat_other_val = $is_std_nat ? '' : $cust_nationality;
@$v_date= stripslashes($row["v_date"]);
$v_time=substr($v_date,11,5);
$v_date=substr($v_date, 0,10);
@$outlet_id= stripslashes($row["outlet_id"]);
$linked_campaign_id = (int)stripslashes($row["campaign_id"]);
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
<script type="text/javascript">
function lookupCustomer() {
	var ic = document.getElementById('cust_ic').value.trim();
	if(!ic){ return; }
	document.getElementById('cust_msg').innerHTML = 'Searching...';
	var xhr = getXMLHTTP();
	xhr.onreadystatechange = function() {
		if(xhr.readyState == 4 && xhr.status == 200) {
			try {
				var r = JSON.parse(xhr.responseText);
				if(r.found) {
					var d = r.data;
					document.getElementById('customer_name').value = d.customer_name || '';
					document.getElementById('phone2').value        = d.phone_num || '';
					document.getElementById('child_num').value     = d.child_num || '';
					document.getElementById('cust_email').value    = d.email || '';
					document.getElementById('cust_addr').value     = d.c_addr || '';
					document.getElementById('cust_language').value = (d.language !== null && d.language !== undefined) ? d.language : '';
					document.getElementById('cust_race').value     = d.race || '';
					var stdNats = ['MALAYSIA','SINGAPORE','INDONESIA','BRUNEI','PHILIPPINES','THAILAND'];
					var nat      = (d.nationality || '').toUpperCase();
					var natSel   = document.getElementById('cust_nationality_sel');
					var natOther = document.getElementById('nat_other');
					var natHid   = document.getElementById('cust_nationality');
					if(stdNats.indexOf(nat) !== -1){
						natSel.value        = nat;
						natOther.style.display = 'none';
						natOther.value      = '';
						natHid.value        = nat;
					} else if(nat !== ''){
						natSel.value        = 'OTHERS';
						natOther.style.display = 'inline';
						natOther.value      = d.nationality;
						natHid.value        = d.nationality;
					} else {
						natSel.value        = '';
						natOther.style.display = 'none';
						natHid.value        = '';
					}
					document.getElementById('cust_diagnosis').value = d.diagnosis || '';
					document.getElementById('cust_allergic').value  = d.allergic || '';
					document.getElementById('cust_msg').innerHTML   = '<span style="color:green;">Found.</span>';
				} else {
					document.getElementById('cust_msg').innerHTML = '<span style="color:red;">Customer not found.</span>';
				}
			} catch(e) {
				document.getElementById('cust_msg').innerHTML = '<span style="color:red;">Error loading customer.</span>';
			}
		}
	};
	xhr.open('GET', 'vaccine_ajax_customer.php?ic=' + encodeURIComponent(ic), true);
	xhr.send();
}

function syncNationality() {
	var sel    = document.getElementById('cust_nationality_sel');
	var other  = document.getElementById('nat_other');
	var hidden = document.getElementById('cust_nationality');
	if(sel.value === 'OTHERS'){
		other.style.display = 'inline';
		hidden.value        = other.value.trim().toUpperCase();
	} else {
		other.style.display = 'none';
		other.value         = '';
		hidden.value        = sel.value;
	}
}

function clearCustErrors() {
	var ids = ['err_language','err_race','err_nationality'];
	for(var i=0; i<ids.length; i++){
		var el = document.getElementById(ids[i]);
		if(el){ el.innerHTML = ''; }
	}
}

function validateCustomerFields() {
	clearCustErrors();
	var lang     = document.getElementById('cust_language').value;
	var race     = document.getElementById('cust_race').value;
	var natSel   = document.getElementById('cust_nationality_sel');
	var natOther = document.getElementById('nat_other');
	var natHid   = document.getElementById('cust_nationality');
	var valid    = true;
	if(!lang){
		document.getElementById('err_language').innerHTML = 'Please select Language.';
		valid = false;
	}
	if(!race){
		document.getElementById('err_race').innerHTML = 'Please select Race.';
		valid = false;
	}
	if(natSel.value === 'OTHERS'){
		if(!natOther.value.trim()){
			document.getElementById('err_nationality').innerHTML = 'Please specify Nationality.';
			valid = false;
		} else {
			natHid.value = natOther.value.trim().toUpperCase();
		}
	} else if(!natSel.value){
		document.getElementById('err_nationality').innerHTML = 'Please select Nationality.';
		valid = false;
	} else {
		natHid.value = natSel.value;
	}
	return valid;
}

function getXMLHTTP() {
		var xmlhttp;
		if (window.XMLHttpRequest)
		{
		xmlhttp=new XMLHttpRequest();
		}
		else
		{
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}

		return xmlhttp;
		}

		function loadCampaigns(outletId) {
			var strURL = "vaccine_ajax.php?campaigns=" + encodeURIComponent(outletId) + "&selected=0";
			var xmlhttp = getXMLHTTP();
			if (xmlhttp) {
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						document.getElementById("camp_container").innerHTML = xmlhttp.responseText;
					}
				};
				xmlhttp.open("GET", strURL, true);
				xmlhttp.send();
			}
		}

		function onCampaignChange(sel) {
			var opt = sel.options[sel.selectedIndex];
			var date = opt.getAttribute('data-date');
			if (date) {
				document.getElementById('v_date').value = date;
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


</script>
<div class="header"><b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
             <h1 class="headerH1"><img src='../common/img/vaccine.png' height='18px'> Edit Transaction</h1>
             <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<div class="idx-panel">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="form1" onsubmit="return validateCustomerFields()">
		<table width="100%" class='myTable'>
			<tr>
				<th>Outlet <span style="color: red;">*</span></th>
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

					echo "<select id='outlet_id' name='outlet_id' onchange='loadCampaigns(this.value)' required>";
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
				<th>Vaccination Date <span style="color: red;">*</span></th>
				<td colspan='3'>
					<input type='date' name='v_date' id='v_date' onkeydown="return tabOnEnter(this,event)" required value='<?php echo $v_date; ?>' />
					<input type='time' name='v_time' id='v_time' onkeydown="return tabOnEnter(this,event)" required value='<?php echo $v_time; ?>' />
				</td>
			</tr>
			<tr>
				<th>Campaign</th>
				<td colspan='3'>
					<span id="camp_container">
					<?php
					$q_camps = "SELECT vc.id, vc.v_date, gc.name, gc.dr_name FROM vaccine_campaign vc LEFT JOIN gp_clinics gc ON vc.clinic=gc.id WHERE vc.outlets='$outlet_id' AND (vc.status != '2' OR vc.id='$linked_campaign_id') AND (vc.v_date >= CURDATE() OR vc.id='$linked_campaign_id') ORDER BY vc.v_date ASC";
					$r_camps = mysqli_query($conn, $q_camps);
					echo "<select id='campaign_id' name='campaign_id' onchange='onCampaignChange(this)'>";
					echo "<option value='' data-date=''>-- No Campaign --</option>";
					if($r_camps){
						while($c = mysqli_fetch_assoc($r_camps)){
							$sel   = ($c['id'] == $linked_campaign_id) ? 'selected' : '';
							$cname = $c['name'] ? $c['name'] : '';
							$cdr   = $c['dr_name'] ? ' ('.$c['dr_name'].')' : '';
							echo "<option value='".$c['id']."' data-date='".$c['v_date']."' $sel>".$c['v_date']." - $cname$cdr</option>";
						}
					}
					echo "</select>";
					?>
					</span>
				</td>
			</tr>

			<tr>
				<th>Vaccine Type <span style="color: red;">*</span></th>
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
				<th>Item Code <span style="color: red;">*</span></th>
				<td colspan='3'><span id='item_code'><?php echo $dropdown_item; ?></span></td>
			</tr>
			<tr>
				<th style="vertical-align:top;">Customer Details</th>
				<td colspan='3'>
					<input type='hidden' name='cust_ic' value='<?php echo htmlspecialchars($ic); ?>' />
					<table style="border:none; background:none;">
						<tr>
							<td style="width:130px; font-weight:bold; padding:3px 6px;">IC</td>
							<td style="padding:3px 6px;">
								<input id='cust_ic' type='text' value='<?php echo htmlspecialchars($ic); ?>' disabled style="background:#f0f0f0;" />
							</td>
						</tr>
						<tr>
							<td style="font-weight:bold; padding:3px 6px;">Full Name</td>
							<td style="padding:3px 6px;">
								<input id='customer_name' name='customer_name' type='text' value='<?php echo htmlspecialchars($customer_name); ?>' style='width:300px;' />
							</td>
						</tr>
						<tr>
							<td style="font-weight:bold; padding:3px 6px;">Phone</td>
							<td style="padding:3px 6px;">
								<input id='phone2' name='phone2' type='text' placeholder='Phone number' value='<?php echo htmlspecialchars($phone_parts[0]); ?>' />@
								<select name='child_num' id='child_num'>
									<option></option>
									<?php
									for ($x = 1; $x <= 10; $x++) {
										if(isset($phone_parts[1]) && $phone_parts[1]==$x){$s='selected';} else {$s='';}
										echo "<option $s>$x</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td style="font-weight:bold; padding:3px 6px;">Email</td>
							<td style="padding:3px 6px;">
								<input id='cust_email' name='cust_email' type='email' value='<?php echo htmlspecialchars($cust_email); ?>' style='width:280px;' />
							</td>
						</tr>
						<tr>
							<td style="font-weight:bold; padding:3px 6px;">Address</td>
							<td style="padding:3px 6px;">
								<textarea id='cust_addr' name='cust_addr' rows='2' cols='45'><?php echo htmlspecialchars($cust_addr); ?></textarea>
							</td>
						</tr>
						<tr>
							<td style="font-weight:bold; padding:3px 6px;">Language <span style="color:red;">*</span></td>
							<td style="padding:3px 6px;">
								<select id='cust_language' name='cust_language'>
									<option value=''>Pick One</option>
									<option value='0' <?php echo ($cust_language==='0')?'selected':''; ?>>BI</option>
									<option value='1' <?php echo ($cust_language==='1')?'selected':''; ?>>BM</option>
									<option value='2' <?php echo ($cust_language==='2')?'selected':''; ?>>BC</option>
								</select>
								<div id='err_language' style='color:red; font-size:11px;'></div>
							</td>
						</tr>
						<tr>
							<td style="font-weight:bold; padding:3px 6px;">Race <span style="color:red;">*</span></td>
							<td style="padding:3px 6px;">
								<select id='cust_race' name='cust_race'>
									<option value=''>Pick One</option>
									<?php echo $race_options; ?>
								</select>
								<div id='err_race' style='color:red; font-size:11px;'></div>
							</td>
						</tr>
						<tr>
							<td style="font-weight:bold; padding:3px 6px;">Nationality <span style="color:red;">*</span></td>
							<td style="padding:3px 6px;">
								<select id='cust_nationality_sel' onchange='syncNationality()'>
									<option value=''>Pick One</option>
									<option value='MALAYSIA' <?php echo ($nat_sel_val==='MALAYSIA')?'selected':''; ?>>MALAYSIA</option>
									<option value='SINGAPORE' <?php echo ($nat_sel_val==='SINGAPORE')?'selected':''; ?>>SINGAPORE</option>
									<option value='INDONESIA' <?php echo ($nat_sel_val==='INDONESIA')?'selected':''; ?>>INDONESIA</option>
									<option value='BRUNEI' <?php echo ($nat_sel_val==='BRUNEI')?'selected':''; ?>>BRUNEI</option>
									<option value='PHILIPPINES' <?php echo ($nat_sel_val==='PHILIPPINES')?'selected':''; ?>>PHILIPPINES</option>
									<option value='THAILAND' <?php echo ($nat_sel_val==='THAILAND')?'selected':''; ?>>THAILAND</option>
									<option value='OTHERS' <?php echo ($nat_sel_val==='OTHERS')?'selected':''; ?>>OTHERS</option>
								</select>
								<input id='nat_other' type='text' placeholder='Specify nationality' value='<?php echo htmlspecialchars($nat_other_val); ?>' oninput='syncNationality()' style='display:<?php echo ($nat_sel_val==="OTHERS")?"inline":"none"; ?>;' />
								<input type='hidden' id='cust_nationality' name='cust_nationality' value='<?php echo htmlspecialchars($cust_nationality); ?>' />
								<div id='err_nationality' style='color:red; font-size:11px;'></div>
							</td>
						</tr>
						<tr>
							<td style="font-weight:bold; padding:3px 6px;">Diagnosis</td>
							<td style="padding:3px 6px;">
								<textarea id='cust_diagnosis' name='cust_diagnosis' rows='3' cols='45'><?php echo htmlspecialchars($cust_diagnosis); ?></textarea>
							</td>
						</tr>
						<tr>
							<td style="font-weight:bold; padding:3px 6px;">Allergic Status</td>
							<td style="padding:3px 6px;">
								<textarea id='cust_allergic' name='cust_allergic' rows='2' cols='45'><?php echo htmlspecialchars($cust_allergic); ?></textarea>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<th>Remark(s)</th>
				<td colspan='3'>
					<textarea name='remark' rows='3' cols='50'><?php echo $remark; ?></textarea>
				</td>
			</tr>
			<tr>
				<th></th>
				<td colspan='3'>
					<input type="submit" name="submit" value="Update" class="upd-submit" />
					<a href="vaccine_index.php" class="upd-back" style="margin-left:8px;">Back</a>
					<input type='hidden' name='trans_id' value='<?php echo $trans_id; ?>' />
				</td>
			</tr>
		</table>
	</form>
</div>
<?php
}
$connect=0;
include ('../common/index_adv.php');
?>
