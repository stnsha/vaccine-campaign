<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<style>
	.btn {
		border: 2px solid black;
		background-color: white;
		color: black;
		padding: 5px 5px;
		border-radius: 5px;
		font-size: 12px;
	}

	.blue {
		border-color: #2196F3;
		color: dodgerblue;
	}

	.green {
		border-color: #4CAF50;
		color: green;
	}

	.orange {
		border-color: #ff9800;
		color: orange;
	}

	.red {
		border-color: #f44336;
		color: red
	}
</style>
<?php
require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');
?>
		<link rel="stylesheet" href="../common/css/jquery-ui.css" type="text/css" >
		<script src="../common/js/jquery-1.5.1.js"></script>
		<script src="../common/js/jquery.ui.core.js"></script>
		<script src="../common/js/jquery.ui.datepicker.js"></script>
		<script>
			$(function() {
				$( "#date_start" ).datepicker({dateFormat: 'yy-mm-dd', changeMonth: true,changeYear: true});
				$( "#date_end" ).datepicker({dateFormat: 'yy-mm-dd', changeMonth: true,changeYear: true});
			});

			function checkAll(checkbox, location) {
				location = location.toLowerCase();
				var CommonLocation = checkbox.parentNode;
				while(CommonLocation.nodeName.toLowerCase() != location && 	CommonLocation != document) {
				CommonLocation = CommonLocation.parentNode;
			}

				var inputs = CommonLocation.getElementsByTagName("input");
				for(var i=0; inputs[i]; i++) {
				if(inputs[i].type == "checkbox") {
				inputs[i].checked = checkbox.checked;
			}
			}
			}

			function view_my_report() {
			   var mapForm = document.getElementById("view_form");
			   mapForm.submit();
			   map=window.open("","Map","status=0,title=0,height=600,width=800,scrollbars=1");

			   if (map) {
				   const table = document.getElementById("myTable");
					const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
				 for (let i = 0; i < rows.length; i++) {
					 console.log(i);
				  const checkbox = rows[i].getElementsByTagName("input")[0];
					const statusCell = rows[i].getElementsByTagName("td")[2];
					console.log(checkbox.checked);
				 }
				  mapForm.submit();
			   } else {
				  alert('You must allow popups for this map to work.');
			   }

			}
		</script>
<?php
//search
$date_start = trim(mysqli_real_escape_string($conn,$_REQUEST['s']));
$date_end = trim(mysqli_real_escape_string($conn,$_REQUEST['e']));
$outlet_id = trim(mysqli_real_escape_string($conn,$_REQUEST['o']));
$status = trim(mysqli_real_escape_string($conn,$_REQUEST['status']));
if($status || $status==0){$option2="and `vaccine_trans_local`.`status`='$status'";}
$type = trim(mysqli_real_escape_string($conn,$_REQUEST['type']));

if($vaccine_autho=='1'){
$query3="select id from outlet where recycle='0' order by code";
$result3=mysqli_query($conn,$query3);
$num3 = mysqli_num_rows ($result3);

if ($num3 > 0 ) {
$i3=0;
$outlet='';
while ($row3 = $result3->fetch_assoc()) {
$o_id = stripslashes($row3['id']);
if($i3=='0'){
$outlet.="$o_id";} else {$outlet.=",$o_id";}
++$i3; } }
}
?>
<div class="header"><b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
             <h1 class="headerH1"><img src='../common/img/download.png'> Generate Referral Letter to Doctor </h1>
             <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<fieldset>
	<div align='left'><a href='vaccine_index.php'><img src='../common/img/refresh.png' width='18px'> Back</a></div>
	<table border="0" cellpadding="4" cellspacing="1" bgcolor="#EFEFEF" width="100%" class='myTable' id='myTable'>
		<form name="form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >
			<tr>
				<th style="background-color:#9999ff" width="150" colspan='3'>
					<div id='excel' align='right'>Vaccination Date Between:</div>
				</th>
				<td colspan='10'>
					<input id="date_start" name="s" type="text" maxlength="10" value='<?php echo $date_start; ?>' size='10' autocomplete='off' onchange='submit();' /> and
					<input id="date_end" name="e" type="text" maxlength="10" value='<?php echo $date_end; ?>' size='10' autocomplete='off' onchange='submit();' />
				</td>
			</tr>
			<tr>
				<th style="background-color:#9999ff" colspan='3'>
					<div align='right'>Outlet :</div>
				</th>
				<td colspan='10'>
					<?php
					$e_outlet = explode(",", $outlet);
					echo "<select name='o' onchange='submit()'>";
					echo "<option value=''>All</option>";
					foreach ($e_outlet as $value) {
					$query2="SELECT id, code FROM `outlet` where id='$value' limit 0,1";
					$result2=mysqli_query($conn,$query2);
					$row2 = $result2 -> fetch_assoc();
					@$id = stripslashes($row2['id']);
					@$code = stripslashes($row2['code']);
					if($outlet_id==$id){$v="selected";} else {$v="";}
					echo "<option $v value='$value'>$code</option>";
					}
					echo "</select>";
					?>
				</td>
			</tr>
			<tr>
				<th style="background-color:#9999ff" colspan='3'>
					<div align='right'>Status :</div>
				</th>
				<td colspan='10'>
					<select name='status' onchange='submit();'>
						<option value='0' <?php if($status=='0'){echo 'selected';} ?> >Pending</option>
					</select>
				</td>
			</tr>
		</form>
		<form id="view_form" name="view_form" method="post" action="vaccine_print_form.php" target="Map" >
			<tr>
				<th style="background-color:#9999ff" width='3%' rowspan='2'>
					<input type="checkbox" id='chkAll' name="chkAll" onclick="checkAll(this, 'table');" checked>
				</th>
				<th width='5%' style="background-color:#9999ff">Num</th>
				<th width='10%' style="background-color:#9999ff">Vaccination Date</th>
				<th width='17%' style="background-color:#9999ff">Customer<br>IC</th>
				<th width='10%' style="background-color:#9999ff">Vaccine</th>
				<th width='15%' style="background-color:#9999ff">Handled by</th>
				<th width='15%' style="background-color:#9999ff">Clinic<br>In Charge</th>
				<th width='15%' style="background-color:#9999ff">Remark</th>
				<th width='10%' style="background-color:#9999ff">Status</th>
			</tr>
			<tbody>
			<?php
			if($outlet_id){$option="and `vaccine_trans_local`.`outlet_id`='$outlet_id'";} else {$option="and `vaccine_trans_local`.`outlet_id` in ($outlet)";}
			$query="SELECT `vaccine_trans_local`.`id`, `vaccine_trans_local`.`timestamp`, `vaccine_trans_local`.`cust_id`, `vaccine_trans_local`.`item_code`, `vaccine_trans_local`.`remark`, `vaccine_trans_local`.`status`, `vaccine_trans_local`.`operator`, `vaccine_trans_local`.`v_date`, `vaccine_trans_local`.`outlet_id`, `vaccine_clinic`.`clinic`, `vaccine_clinic`.`dr_name` FROM `vaccine_trans_local` left join vaccine_clinic on vaccine_trans_local.clinic=vaccine_clinic.id where `vaccine_trans_local`.`recycle`=0 and `vaccine_trans_local`.`v_date` between '$date_start 00:00:00' and '$date_end 23:59:59' $option $option2 order by vaccine_trans_local.v_date, vaccine_trans_local.outlet_id, vaccine_trans_local.cust_id";
			$result=mysqli_query($conn,$query);
			$num = mysqli_num_rows ($result);
			if ($num > 0 ) {
			$i=0;
			while ($row = $result->fetch_assoc()) {
			$trans_id = stripslashes($row['id']);
			$timestamp = stripslashes($row['timestamp']);
			$cust_id = stripslashes($row['cust_id']);
				//search for customer name, IC, and phone
				$query3="select `customer_name`, `ic`, `phone` from `customer` where `id`='$cust_id' limit 0,1";
				$result3 = mysqli_query($conn, $query3);
				$row3 = $result3 -> fetch_assoc();
				@$customer_name= stripslashes($row3["customer_name"]);
				@$ic= stripslashes($row3["ic"]);
				@$phone= stripslashes($row3["phone"]);
				$hp=preg_replace('/\D/', '', $phone);
				$prefix='60';
				$prefix2='6';
				if(substr("$hp", 0, 2)=='01'){$new="<a href='javascript:void(0);' onclick=\"window.open(&quot;https://api.whatsapp.com/send?phone=$prefix2$hp&text=Dear%20$dr_name,%0A%0A&language=en&quot;,&quot;Ratting&quot;,&quot;width=1,height=1,left=1,top=1,toolbar=no,scrollbars=no,menubar=no,resizable=no&quot;);\" title='Send Whatsapp'><img src='../common/img/wa.png' width='15px'>$hp</a><br/>";} else if(substr("$hp", 0, 1)=='1'){$new="<a href='javascript:void(0);' onclick=\"window.open(&quot;https://api.whatsapp.com/send?phone=$prefix2$hp&text=Dear%20$dr_name,%0A%0A&language=en&quot;,&quot;Ratting&quot;,&quot;width=1,height=1,left=1,top=1,toolbar=no,scrollbars=no,menubar=no,resizable=no&quot;);\" title='Send Whatsapp'><img src='../common/img/wa.png' width='15px'>$hp</a><br/>";} else {$new="$hp";}
			$outlet_id = stripslashes($row['outlet_id']);
				//search for outlet code
				$query3="SELECT `code` FROM `outlet` WHERE `id`='$outlet_id' limit 0,1";
				$result3 = mysqli_query($conn, $query3);
				$row3 = $result3 -> fetch_assoc();
				@$code= stripslashes($row3["code"]);
			$item_code = stripslashes($row['item_code']);
				//search for item description
				$query3="SELECT `name` FROM `simple` WHERE `item_code`='$item_code' limit 0,1";
				$result3 = mysqli_query($conn, $query3);
				$row3 = $result3 -> fetch_assoc();
				@$description= stripslashes($row3["name"]);
			$remark = stripslashes($row['remark']);
			$status = stripslashes($row['status']);
			$operator = stripslashes($row['operator']);
				//search for staff name
				$query4="SELECT `nama_staff` FROM `staff` WHERE `id`='$operator' limit 0,1";
				$result4 = mysqli_query($conn, $query4);
				$row4 = $result4 -> fetch_assoc();
				@$staff_name= stripslashes($row4["nama_staff"]);
			$v_date = stripslashes($row['v_date']);
			$clinic = stripslashes($row['clinic']);
			$dr_name = stripslashes($row['dr_name']);

			//calculate numbering asc
			$r=$i+1;
			?>
			<tr valign='top'>
				<td align='center' rowspan='<?php echo $num7; ?>'><input name='bulk_print[]' value='<?php echo $trans_id; ?>' type='checkbox' checked /></td>
				<td align='center' rowspan='<?php echo $num7; ?>'>
					<?php echo $r; ?>.
				</td>
				<td align='center'><?php echo $v_date; ?></td>
				<td align='center'><?php echo "$customer_name<br>$ic<br>$new"; ?></td>
				<td align='center'><?php echo "$item_code<br>$description"; ?></td>
				<td align='center' title='<?php echo $timestamp; ?>'><?php echo $staff_name; ?><br>(<?php echo $code; ?>)</td>
				<td align='center'><?php echo "$clinic<br>$dr_name"; ?></td>
				<td><?php echo $remark; ?></td>
				<td align='center'>
					<?php
					if($status==0){echo "Pending";} else if($status==1){echo "Vaccinated";} else if($status==2){echo "Referred to Doctor";} else if($status==3){echo "Cancelled";}
					?>
				</td>
			</tr>
			<?php
			$i++;
			}}
			?>
			</tbody>
			<tr>
				<td colspan='13' align='center'>
					<select name='print_type'>
						<option value='1' <?php if($type=='1'){echo 'selected';} ?> >Referral Letter</option>
					</select>
					<input type='hidden' name='staff' value='<?php echo $nama_staff; ?>' />
					<input type='hidden' name='position' value='<?php echo $status_semasa; ?>' />
					<input type='submit' name='submit' value='Generate' onclick="view_my_report();"  />
				</td>
			</tr>
		</form>
</table>
</fieldset>
<?php
$connect=0;
include ('../common/index_adv.php');
?>
