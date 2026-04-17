<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<style>
	@media print{
	table { page-break-after:auto }
	tr    { page-break-inside:avoid; page-break-after:auto }
	td    { page-break-inside:avoid; page-break-after:auto }
	@page {
		margin-top: 0px;
		margin-bottom: 15px;
		margin-right: 25px;
		margin-left: 25px;
		size: portrait;
		max-height:100%;
		max-width:100%
		margin-bottom: 0.5cm;
		box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
		-webkit-transform: rotate(90deg);
		}
	}
	@page {
	  size: A4;
	}
	table.report-container {
		page-break-after:always;
		width: 500px;
		margin: auto;
	}
	thead.report-header {
		display:table-header-group;
	}
	tfoot.report-footer {
		text-align:center;
		display:table-footer-group;
	}

	table.report-container div.article {
		page-break-inside: avoid;
	}

	.table {
		border: 1px solid black;
		border-collapse: collapse;
		width:100%;
	}

	.table th, .table td {
	border: 1px solid black;
    border-collapse: collapse;
	}

	 /* Style for the container div */
        .container {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Two columns with equal width */
            gap: 10px; /* Optional gap between the columns */
        }
</style>
<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
$connect=1;
include ('../common/index_adv.php');
include ('../common/my_api.php');
//set limit for memory
ini_set('memory_limit', '128M');

//set limit for time
ini_set('max_execution_time', 6000);

$bulk_print =$_POST['bulk_print'];
$print_type =$_POST['print_type'];
$per_print_list= implode(",",$bulk_print);
$position =$_POST['position'];
$staff =$_POST['staff'];

if($print_type=='1'){
	?>
	<script type="text/javascript">
	 <!--
	 function printpage() {
	 window.print();
	 }
	 //-->
	</script>
	<body onload='printpage()'>
<table class="report-container" border='0'>
   <thead class="report-header">
     <tr>
        <th class="report-header-cell">
           <div class="header-info">
				<img src='../common/img/sugo_header.png' width='900px'>
           </div>
         </th>
      </tr>
    </thead>
    <tfoot class="report-footer">
      <tr>
         <td class="report-footer-cell">
           <div class="footer-info">
				<img src='../common/img/sugo_footer.png' width='900px'>
           </div>
          </td>
      </tr>
    </tfoot>
	<tbody class="report-content">
	<tr>
			<td class="report-content-cell">
	<?php
		//form clinic_array
		$query2="SELECT clinic, outlet_id FROM `vaccine_trans` where `vaccine_trans`.`recycle`=0 and `vaccine_trans`.`id` in ($per_print_list) and status=0 group by clinic";
		$result2=mysqli_query($conn,$query2);
		$num2 = mysqli_num_rows($result2);
		if($num2>0){
			while ($row2 = $result2->fetch_assoc()) {
				$clinic_id = stripslashes($row2['clinic']);
				$outlet_id = stripslashes($row2['outlet_id']);
				$clinic_array[]=array($clinic_id, $outlet_id);
			}
		}

		if(is_array($clinic_array)){
			foreach ($clinic_array as $data){
			$clinic_id=$data[0];
			$outlet_id=$data[1];

			//search for outlet info
			$query4="SELECT `code`, `comp_name`, `office1`, `office2`, `addr` FROM `outlet` WHERE `id`='$outlet_id' limit 0,1";
			$result4 = mysqli_query($conn, $query4);
			$row4 = $result4 -> fetch_assoc();
			@$code= stripslashes($row4["code"]);
			@$comp_name= stripslashes($row4["comp_name"]);
			@$office1= stripslashes($row4["office1"]);
			@$office2= stripslashes($row4["office2"]);
			@$addr= stripslashes($row4["addr"]);
			$addr=nl2br(trim($addr));

			//search clinic info
			$query3="SELECT * FROM `vaccine_clinic` WHERE `id`='$clinic_id' limit 0,1";
			$result3 = mysqli_query($conn, $query3);
			$row3 = $result3 -> fetch_assoc();
			@$clinic= stripslashes($row3["clinic"]);
			@$c_phone= stripslashes($row3["c_phone"]);
			@$dr_name= stripslashes($row3["dr_name"]);
			@$address= stripslashes($row3["address"]);
			$address=nl2br(trim($address));

				echo "<div class='article'><br><div><b>$clinic</b></div><div>$address</div><div class='container'><div>$c_phone</div><div align='right'>".date('d-m-Y')."</div></div>";
			?>
			<br><br>
			Dear <?php echo $dr_name; ?>,<br><br>
			I am writing to refer a group of patients to your esteemed clinic for the administration of vaccinations. The patients listed below require vaccination, and we have obtained the necessary supply of vaccines from our pharmacy to facilitate this process.<br><br>
			<table style='width: 100%; border-collapse: collapse;' border='1'>
			<tr align='top'>
				<th width='3%'>Num</th>
				<th width='10%'>Vaccination Date</th>
				<th width='20%'>Customer/IC</th>
				<th width='15%'>Contact</th>
				<th width='32%'>Vaccine</th>
				<th width='15%'>Batch/Expiry</th>
				<th width='5%'>Outlet</th>
			</tr>
			<?php
			$query="SELECT `vaccine_trans`.`id`, `vaccine_trans`.`timestamp`, `vaccine_trans`.`cust_id`, `vaccine_trans`.`item_code`, `vaccine_trans`.`batch_num`, `vaccine_trans`.`expiry_date`, `vaccine_trans`.`status`, `vaccine_trans`.`v_date`, `vaccine_trans`.`outlet_id` FROM `vaccine_trans` where `vaccine_trans`.`recycle`=0 and `vaccine_trans`.`id` in ($per_print_list) and `vaccine_trans`.`status`=0 and `vaccine_trans`.`clinic`='$clinic_id' order by vaccine_trans.v_date, vaccine_trans.outlet_id, vaccine_trans.cust_id";
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
				if(substr("$hp", 0, 2)=='01'){$new="$prefix2$hp";} else if(substr("$hp", 0, 1)=='1'){$new="$prefix$hp";} else {$new="$hp";}
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
			$batch_num = stripslashes($row['batch_num']);
			$expiry_date = stripslashes($row['expiry_date']);
			$status = stripslashes($row['status']);
			$v_date = stripslashes($row['v_date']);

			//calculate numbering asc
			$r=$i+1;
			?>
			<tr valign='top'>
				<td align='center'><?php echo $r; ?>.</td>
				<td align='center'><?php echo $v_date; ?></td>
				<td align='center'><?php echo "$customer_name<br>($ic)"; ?></td>
				<td align='center'><?php echo "$new"; ?></td>
				<td align='center'><?php echo "$item_code<br>$description"; ?></td>
				<td align='center'><?php echo "$batch_num<br>(exp:$expiry_date)"; ?></td>
				<td align='center' title='<?php echo $timestamp; ?>'><?php echo $code; ?></td>
			</tr>
			<?php
			$i++;
			}}
			?>
			<tr><td colspan='5' align='center'>Total Count:</td><td align='center' colspan='2'><?php echo $num; ?></td></tr>
		</table>
		<br><br>
		<?php echo $clinic; ?> has agreed to receive the quantity of vaccine(s) as listed in the table above. The clinic will only charge RM10 for each injection when customer in the name list above attend to the clinic.<br><br>Thank you.<br><br><br>

		<br><br><br>
		<table width='100%' border='0'>
			<tr>
				<td width='40%' valign='top'>Sincerely,<br><br><br> ___________<br><?php echo $staff; ?><br>(<?php echo $position; ?>)<br><?php echo "$comp_name<br>$addr<br>$office1 $office2</div>"; ?></td><td></td><td width='40%' valign='top'>Received by,<br><br><br> ___________<br><?php echo $dr_name; ?><br>(Medical Doctor)<br><?php echo "$clinic<br>$address<br>$c_phone</div>"; ?></td>
			</tr>
		</table>


<?php }}
else {echo "No data found!"; exit;}
}
echo "</td>
	</tr></tbody>
</table>
</body>";
?>
