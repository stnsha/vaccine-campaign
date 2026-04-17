<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');

if(isset($_POST['submit']))  {
//set limit for memory
ini_set('memory_limit', '2000M');

//set limit for time
ini_set('max_execution_time', 30000);

//check date
function convertDateFormat($date, $outputFormat = 'Y-m-d') {
    $normalizedDate = str_replace(array('-', '.'), '/', $date);
    $inputFormats = array('n/j/Y', 'j/n/Y', 'm/d/Y', 'd/m/Y', 'Y/m/d');
    foreach ($inputFormats as $format) {
        $d = DateTime::createFromFormat($format, $normalizedDate);
        if ($d && $d->format($format) == $normalizedDate) {
            return $d->format($outputFormat);
        }
    }
    return false;
}

function convertDateTimeFormat($date, $time) {
    $timeObject = DateTime::createFromFormat('h:iA', $time);
    if (!$timeObject) return false;
    $time24h = $timeObject->format('H:i:s');
    $normalizedDate = str_replace(array('-', '.'), '/', $date);
    $inputFormats = array('n/j/Y', 'j/n/Y', 'm/d/Y', 'd/m/Y', 'Y/m/d');
    foreach ($inputFormats as $format) {
        $dateObject = DateTime::createFromFormat($format, $normalizedDate);
        if ($dateObject && $dateObject->format($format) == $normalizedDate) {
            $formattedDate = $dateObject->format('Y-m-d');
            return "$formattedDate $time24h";
        }
    }
    return false;
}

//search for ic_arr
$query2="select `id`, `ic`, `phone` from `customer`";
$result2=mysqli_query($conn,$query2);
$num2 = mysqli_num_rows ($result2);
$ic_arr=array();
$phone_arr=array();
if ($num2 > 0 ) {
	while ($row2 = $result2->fetch_assoc()) {
	$cust_id = stripslashes($row2['id']);
	$ic = stripslashes($row2['ic']);
	$phone = stripslashes($row2['phone']);
	$ic_arr[$ic]=$cust_id;
	$phone_arr[$phone]=1;
}}

//form item_arr
$query2="SELECT `item_code` FROM `vaccine_code`";
$result2=mysqli_query($conn,$query2);
$num2 = mysqli_num_rows ($result2);
$item_arr=array();
if ($num2 > 0 ) {
	while ($row2 = $result2->fetch_assoc()) {
	$item_code = stripslashes($row2['item_code']);
	$item_arr[$item_code]=1;
}}

$outlet_id = trim(mysqli_real_escape_string($conn,$_POST['outlet_id']));
$clinic = trim(mysqli_real_escape_string($conn,$_POST['clinic']));
$clinic_part=explode(":",$clinic);
$clinic_id=trim($clinic_part[0]);
$fileName = $_FILES['userfile']['name'];
$tmpName = $_FILES['userfile']['tmp_name'];
$fileSize = $_FILES['userfile']['size'];
$fileType = $_FILES['userfile']['type'];
$ext = strtolower(substr(strrchr($fileName, "."), 1));

echo "<fieldset>";
if($ext!='csv'){echo "<fieldset class='center'><img src='../common/img/warning.png'><br/>Unsupported file type!<br/><a href='vaccine_import.php'><img src='../common/img/refresh.png'> Back</a></fieldset>";
$connect=0;
include ('../common/index_adv.php');
exit;}

if ($fileSize > 0) {
 $handle = fopen("$tmpName", "r");
        fgetcsv($handle);
		fgetcsv($handle);
		$line=3;
		$succeed=0;
		$failed=0;
		// Cache resolved campaign IDs per outlet+date to avoid redundant DB queries
		$campaign_cache = array();
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$customer_name=trim($data[0]);
            $ic=$data[1];
			$phone=$data[2];
			$v_date=$data[3];
			$v_time=$data[4];
			$item_code=$data[5];
			$batch_num=$data[6];
			$expiry_date=$data[7];
			$remark=$data[8];
			$v_date=convertDateTimeFormat("$v_date", "$v_time");

		// Store all required fields in an array
		$required_fields = array($customer_name, $ic, $v_date, $v_time, $item_code, $batch_num, $expiry_date);

		//omit if no name
		if($customer_name){

		// Check if any field is empty
		if (in_array("", $required_fields, true) || in_array(null, $required_fields, true)) {
			echo "Line $line: Missing required field.<br>"; $failed++;;
		} else {

			if(!$v_date){echo "Line $line: Invalid vaccination date.<br>"; $failed++;} else {

			$expiry_date=convertDateFormat($expiry_date);
			if(!$expiry_date){echo "Line $line: Invalid expiry date.<br>"; $failed++;} else {

			if(isset($item_arr[$item_code])){

			//search or insert cust id
				$customer_name=ucwords(strtolower($customer_name));

				$phone2=preg_replace('/\D/', '', $phone);
				if(strlen($phone2)>=9){

			//detect duplicated phone num
				$ic = preg_replace('/\D/', '', $ic);
				if(strlen($ic)==12){
					//check if ic existed
					if(isset($ic_arr[$ic])){$cust_id=$ic_arr[$ic]; $insert=1;} else {
						//check if phone num existed
						if(!isset($phone_arr[$phone])){

						//calculate birth date
						$birth_year = substr("$ic", 0, 2);
						$birth_month = substr("$ic", 2, 2);
						$birth_day = substr("$ic", 4, 2);
						if($birth_year<=99 and $birth_year>30){$birth_year2="19$birth_year";} else {$birth_year2="20$birth_year";}
						$birth_date = "$birth_year2-$birth_month-$birth_day";

						//identify gender
						$gender_num = substr("$ic",-1);
						if( $odd = $gender_num%2 )
						{
							$gender='Male';
						}
						else
						{
							$gender='Female';
						}
						//register customer and get cust_id
						$customer_name_esc = mysqli_real_escape_string($conn, $customer_name);
						$phone_esc = mysqli_real_escape_string($conn, $phone);
						$query = "INSERT INTO  `odb`.`customer` (
								`id` ,
								`c_id` ,
								`date` ,
								`customer_name` ,
								`ic` ,
								`gender` ,
								`birth_date` ,
								`allergic` ,
								`diagnosis` ,
								`language` ,
								`phone` ,
								`email` ,
								`c_addr`,
								`operator`,
								`race`
								)
								VALUES (
								'null',  '',  NOW(),  '$customer_name_esc',  '$ic',  '$gender', '$birth_date',  '',  '', '0', '$phone_esc', '', '', '$id_user', ''
								)";
						$results = mysqli_query($conn,$query);
						$phone_arr[$phone]=1;
						$cust_id=mysqli_insert_id($conn);
						$insert=1;
						} else {echo "Line $line: Duplicated phone num found.<br>"; $failed++; $insert=0;}
					}

					if($insert==1){
						//prevent duplication
						$sql2="select id from `vaccine_trans_local` where `outlet_id`='$outlet_id' and `v_date`='$v_date' and `cust_id`='$cust_id' and `item_code`='$item_code' and recycle=0";
						$result2 = mysqli_query($conn, $sql2);
						$num=mysqli_num_rows($result2);
						$row2 = $result2 -> fetch_assoc();
						@$trans_id= stripslashes($row2["id"]);
						if(!$trans_id){

							// Resolve campaign: create if none exists for this outlet+date, else use existing
							$date_only = substr($v_date, 0, 10);
							$cache_key = "$outlet_id:$date_only";
							if(isset($campaign_cache[$cache_key])){
								$campaign_id = $campaign_cache[$cache_key];
							} else {
								$camp_q = "SELECT id FROM vaccine_campaign WHERE outlets='$outlet_id' AND v_date='$date_only' LIMIT 1";
								$camp_r = mysqli_query($conn, $camp_q);
								$camp_row = $camp_r ? mysqli_fetch_assoc($camp_r) : null;
								if($camp_row){
									$campaign_id = (int)stripslashes($camp_row['id']);
								} else {
									$camp_ins = "INSERT INTO vaccine_campaign (id, v_date, outlets, clinic, type, status) VALUES (NULL, '$date_only', '$outlet_id', '$clinic_id', '2', '1')";
									mysqli_query($conn, $camp_ins);
									$campaign_id = (int)mysqli_insert_id($conn);
								}
								$campaign_cache[$cache_key] = $campaign_id;
							}

							$item_code_esc = mysqli_real_escape_string($conn, $item_code);
							$batch_num_esc = mysqli_real_escape_string($conn, $batch_num);
							$remark_esc = mysqli_real_escape_string($conn, $remark);
							$query="INSERT INTO `vaccine_trans_local` (`id`, `timestamp`, `v_date`, `cust_id`, `item_code`, `clinic`, `batch_num`, `expiry_date`, `outlet_id`, `remark`, `status`, `operator`, `campaign_id`) VALUES (NULL, NOW(), '$v_date', '$cust_id', '$item_code_esc', '$clinic_id', '$batch_num_esc', '$expiry_date', '$outlet_id', '$remark_esc', '0', '$id_user', '$campaign_id')";
							$result=mysqli_query($conn, $query);
							if($result){$succeed++;} else {$failed++;}
						} else {
							echo "Line $line: Duplication of record found!<br>";
							$failed++;
						}
					}
				} else {echo "Line $line: Wrong IC format.<br>"; $failed++;}
				} else {echo "Line $line: Invalid Phone Num.<br>"; $failed++;}
			} else {echo "Line $line: Incorrect vaccine code.<br>"; $failed++;}
			}
			}
			}
			}
		$line++;
		}
        fclose($handle);
    } else {
        echo "File is empty!";
    }
	echo "Succeed: $succeed<br> Failed: $failed";
	echo "</fieldset>";
} else {
?>
<script type="text/javascript" src="../common/js/jquery-1.5.1.js"></script>
<script type='text/javascript' src="../common/js/jquery.autocomplete.js"></script>
<link rel="stylesheet" type="text/css" href="../common/css/jquery.autocomplete.css" />
<script type="text/javascript">
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
});
</script>
<div class="header" style="position: relative;">
			<b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
            <h1 class="headerH1"><img src='../common/img/target.png' width='20px'> Import Vaccine Transactions </h1>
            <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<fieldset>
	<form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<table width="100%" border="0" cellspacing="2" cellpadding="0" class='myTable'>
			<tr>
				<th>
					<div align="right"><b>CSV File (.csv):</b></div>
				</th>
				<td>
					<input name="userfile" type="file" required>
				</td>
			</tr>
			<tr>
				<th><div align="right">Outlet :</div></th>
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

					echo "<select id='outlet_id' name='outlet_id' required>";
					echo "<option value=''>Pick One</option>";
					foreach ($e_outlet as $value) {
					$query2="SELECT code FROM `outlet` where id='$value' limit 0,1";
					$result2=mysqli_query($conn,$query2);
					$row2 = $result2 -> fetch_assoc();
					@$code = stripslashes($row2['code']);
					echo "<option value='$value'>$code</option>";
					}
					echo "</select>";
				?>
				</td>
			</tr>
			<tr>
				<th><div align="right">Clinic :</div></th>
				<td colspan='3'>
					<input id='clinic' name='clinic' placeholder='Clinic Name' size='50' autocomplete="off" value='' required autofocus onkeydown="return tabOnEnter(this,event)" />
				</td>
			</tr>
			<tr><td></td><td><a href='template.csv'><img src='../common/img/download.png' width='12px'>Template</a></td></tr>
			<tr>
				<td></td>
				<td>
					<input name="submit" type="submit" class="box" id="submit" value="Upload">
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
