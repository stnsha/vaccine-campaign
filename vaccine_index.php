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

.idx-pagination {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-family: Arial, Helvetica, sans-serif;
}

.idx-pagination img {
    height: 17px;
    width: auto;
}

.idx-pagination span {
    font-size: 13px;
}

.idx-pagination a {
    display: inline-flex;
    align-items: center;
    opacity: 0.7;
    text-decoration: none;
}

.idx-pagination a:hover {
    opacity: 1;
    text-decoration: none;
}

.idx-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}

.idx-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.idx-filters {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.idx-filters select,
.idx-filters input[type="date"],
.idx-filters input[type="text"] {
    border-radius: 8px;
    padding: 5px 8px;
    border: 1px solid #cfcfcf;
    background: #fff;
    font-size: 12px;
    font-family: Arial, Helvetica, sans-serif;
    line-height: 1.4;
    box-sizing: border-box;
    height: 32px;
}

.idx-filters select:focus,
.idx-filters input:focus {
    outline: none;
    border-color: rgba(0, 91, 150, .55);
    box-shadow: 0 0 0 3px rgba(0, 91, 150, .12);
}

.idx-icon-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 8px;
    line-height: 1.4;
    box-sizing: border-box;
    text-decoration: none;
    opacity: 0.7;
    position: relative;
}

.idx-icon-link:hover {
    opacity: 1;
    text-decoration: none;
}

.idx-icon-link img {
    height: 17px;
    width: auto;
}

.idx-icon-link::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: #fff;
    font-size: 11px;
    white-space: nowrap;
    padding: 4px 8px;
    border-radius: 5px;
    pointer-events: none;
    opacity: 0;
    transition: opacity .15s ease;
    z-index: 10;
}

.idx-icon-link:hover::after {
    opacity: 1;
}

.idx-sep {
    display: inline-block;
    border-left: 2px solid #d0d7de;
    height: 16px;
    vertical-align: middle;
}

.idx-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    padding: 5px 12px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    line-height: 1.4;
    font-family: Arial, Helvetica, sans-serif;
    box-shadow: 0 2px 6px rgba(0, 0, 0, .10);
    box-sizing: border-box;
}

.idx-btn:hover {
    text-decoration: none;
    box-shadow: 0 3px 10px rgba(0, 0, 0, .15);
}

.idx-btn-reset,
a.idx-btn-reset {
    background: #005B96 !important;
    color: #fff !important;
    border-color: #005B96 !important;
    font-weight: bold !important;
}

.idx-btn-reset:hover,
a.idx-btn-reset:hover {
    background: #004d80 !important;
    color: #fff !important;
}

/* Table */
#view_form {}

.myTable {
    font-size: 13px;
}

.myTable th {
    font-size: 13px;
    text-align: left;
}

.myTable td {
    text-align: left !important;
    font-size: 13px;
    vertical-align: middle;
}

.myTable a {
    text-decoration: none !important;
}

.myTable a:hover {
    text-decoration: none;
}

.myTable input[type="text"],
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
.myTable select:focus,
.myTable textarea:focus {
    outline: none;
    border-color: rgba(0, 91, 150, .55);
    box-shadow: 0 0 0 3px rgba(0, 91, 150, .12);
}
</style>
<?php
require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');
?>
<script>
function getXMLHTTP() { //fuction to return the xml http object
    var xmlhttp;
    if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else { // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }

    return xmlhttp;
}

//update qty and remark
function updateData(columnName, columnValue, idNum) {
    var strURL = "vaccine_ajax.php?n=" + columnName + "&v=" + columnValue + "&t_id=" + idNum
    var xmlhttp = getXMLHTTP();
    if (xmlhttp) {
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("feedbackdiv" + idNum).innerHTML = xmlhttp.responseText;
            }
        }
        xmlhttp.open("GET", strURL, true);
        xmlhttp.send();
    }
}

function whatsapp(phone, transId, language, cName, vDate, oLocation, clinic, outletPhone) {
    currentStatus = document.getElementById("status" + transId).value;
    var msg = '';
    if (currentStatus == 0) {
        if (language == '0') {
            msg = "Hi " + cName + ",%0A%0ADon't forget your vaccine appointment at " + clinic + " on " + vDate +
                ". See you soon! %0A%0ABest regards,%0AAlpro Pharmacy";
        } else if (language == '1') {
            msg = "Hi " + cName + ", %0A%0AJangan lupa temujanji vaksin anda di " + clinic + " pada " + vDate +
                ". Jumpa anda nanti! %0A%0ASalam hormat,%0AFarmasi Alpro";
        } else {
            msg = "你好 " + cName + "，%0A%0A温馨提心: 不要忘了在 " + clinic + " 于" + vDate + " 的疫苗接种预约。不见不散！%0A%0A此致，%0A大专药剂";
        }
    } else if (currentStatus == 1) {
        if (language == '0') {
            msg = "Dear " + cName +
                ",%0A%0ASome tips for you after your flu vaccine: %0A%0A Stay hydrated.%0A Enjoy a light meal.%0A Avoid strenuous activities.%0A%0AIn case of any concerns or fever, don't hesitate to reach out to us at https://wa.me/6" +
                outletPhone + ".%0A%0ABest regards,%0AAlpro Pharmacy";
        } else if (language == '1') {
            msg = "Kepada " + cName +
                " yang dihormati,%0A%0ABeberapa tips untuk anda selepas menerima vaksin flu: %0A%0A Pastikan anda minum banyak air.%0A Nikmati makanan ringan.%0A Elakkan aktiviti lasak.%0A%0AJika ada sebarang kebimbangan atau demam, sila hubungi kami di https://wa.me/6" +
                outletPhone + "%0A%0ASalam hormat,%0AFarmasi Alpro";
        } else {
            msg = "亲爱的" + cName +
                "，%0A%0A流感疫苗接种后的一些建议：%0A%0A 保持水分充足。%0A 享用清淡的餐点。%0A 避免剧烈活动。%0A%0A如果有任何疑虑或出现发烧的症状，请与我们联系，电话： https://wa.me/6" +
                outletPhone + "%0A%0A最诚挚的问候，%0A大专药剂";
        }
    } else if (currentStatus == 2) {
        if (language == '0') {
            msg = "Dear " + cName + ",%0A%0AHi there! We noticed you missed the vaccine on " + vDate +
                ", but not to worry! You can still get vaccinated at " + clinic +
                " within one month. When you visit the clinic, just provide your name and show this message to the doctor during operating hours, with an additional charge of RM10.%0A%0ABest regards,%0AAlpro Pharmacy";
        } else if (language == '1') {
            msg = "Kepada " + cName + " yang dihormati,%0A%0AHi! Kami perasan anda terlepas vaksin pada " + vDate +
                ", tetapi jangan risau! Anda masih boleh mendapatkan vaksin di " + clinic +
                " dalam masa satu bulan. Apabila anda mengunjungi klinik, hanya berikan nama anda dan tunjukkan mesej ini kepada doktor semasa waktu operasi, dengan caj tambahan RM10.%0A%0ASalam hormat,%0AFarmasi Alpro";
        } else {
            msg = "亲爱的" + cName + "，%0A%0A你好！我们发现你错过了在" + vDate + "的疫苗接种，但不用担心！%0A你仍然可以在一个月内在" + clinic +
                " 接种疫苗。当你来到诊所时，只需提供您的姓名并在营业时间内向医生展示此信息，额外收费为RM10。%0A%0A最诚挚的问候，%0A大专药剂";
        }
    } else {
        $msg = '';
    }

    window.open("https://api.whatsapp.com/send?phone=" + phone + "&text=" + msg +
        "&quot;,&quot;Ratting&quot;,&quot;width=1,height=1,left=1,top=1,toolbar=no,scrollbars=no,menubar=no,resizable=no"
    );
}
</script>
<?php
//form company arr
$query2="SELECT `id`, `company_name` FROM `finance_expenses_gl`";
$result2=mysqli_query($conn,$query2);
$num2 = $result2 ? mysqli_num_rows ($result2) : 0;
$comp_arr=array();
if ($num2 > 0 ) {
	$i2=0;
	while ($row2 = $result2->fetch_assoc()) {
	$comp_id = stripslashes($row2['id'] ?? '');
	$company_name = stripslashes($row2['company_name'] ?? '');
	$comp_arr[$comp_id]=$company_name;
	$i2++;
}}

if($vaccine_autho=='1' || strpos($status_semasa, "Doctor") !== false){
$query3="select id from outlet where recycle='0' order by code";
$result3=mysqli_query($conn,$query3);
$num3 = $result3 ? mysqli_num_rows ($result3) : 0;

$outlet='';
if ($num3 > 0 ) {
$i3=0;
while ($row3 = $result3->fetch_assoc()) {
$o_id = stripslashes($row3['id'] ?? '');
if($i3=='0'){
$outlet.="$o_id";} else {$outlet.=",$o_id";}
++$i3; } }
}

//delete
if(isset($_GET['d'])){
	$del_id = trim(mysqli_real_escape_string($conn, $_GET['id'] ?? ''));
	$query2="update `vaccine_trans` set `recycle`=1 where `id`='$del_id'";
	$result2=mysqli_query($conn, $query2);
}

//search
$option='';
$option1='';
$option2='';
$option3='';
$key='';
$search_status='';
$date_from=date('Y-m-01');
$date_to=date('Y-m-d');
if(isset($_REQUEST['s'])){
$key = trim(mysqli_real_escape_string($conn, $_REQUEST['key'] ?? ''));
$search_status = trim(mysqli_real_escape_string($conn, $_REQUEST['search_status'] ?? ''));
if(isset($_REQUEST['date_from']) && $_REQUEST['date_from']!==''){
	$date_from=trim(mysqli_real_escape_string($conn,$_REQUEST['date_from']));
}
if(isset($_REQUEST['date_to']) && $_REQUEST['date_to']!==''){
	$date_to=trim(mysqli_real_escape_string($conn,$_REQUEST['date_to']));
}

if($key){
	//search for possible customer
	$query5="SELECT `id` FROM `customer` where `customer_name` like '%$key%' or `ic` like '%$key%'";
	$result5 = mysqli_query($conn, $query5);
	$num5= $result5 ? mysqli_num_rows($result5) : 0;
	$cust_id_arr=array();
	if($num5>0){
		while ($row5 = $result5->fetch_assoc()) {
			$cust_id_arr[] = stripslashes($row5['id'] ?? '');
		}
	}
	if(!empty($cust_id_arr)){
		$cust_id_csv=implode(",",$cust_id_arr);
		$option2="and (`cust_id` in ($cust_id_csv) or `inv_num` like '%$key%')";
	} else {
		$option2="and `inv_num` like '%$key%'";
	}
	}
	if($search_status !== ''){
		$option3="and `vaccine_trans`.`status`='$search_status'";
	}
}

$outlet_id = trim(mysqli_real_escape_string($conn, $_REQUEST['outlet_id'] ?? ''));
if($outlet_id){
	$option1="and `outlet_id`='$outlet_id'";
} else {
	$option1="and `outlet_id` in ($outlet)";
}
$option4="and DATE(`vaccine_trans`.`v_date`) between '$date_from' and '$date_to'";
$option="$option1 $option2 $option3 $option4";

//get page no
if (isset($_REQUEST['pageno'])) {
   $pageno = $_REQUEST['pageno'];
} else {
   $pageno = 1;
}

//Search total page no
$query = "SELECT count(*) FROM `vaccine_trans` left join gp_clinics on vaccine_trans.clinic=gp_clinics.id where `vaccine_trans`.`recycle`=0 $option";
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

$query="SELECT `vaccine_trans`.`id`, `vaccine_trans`.`timestamp`, `vaccine_trans`.`cust_id`, `vaccine_trans`.`item_code`, `vaccine_trans`.`batch_num`, `vaccine_trans`.`expiry_date`, `vaccine_trans`.`remark`, `vaccine_trans`.`status`, `vaccine_trans`.`inv_num`, `vaccine_trans`.`operator`, `vaccine_trans`.`v_date`, `vaccine_trans`.`outlet_id`, `gp_clinics`.`name`, `gp_clinics`.`dr_name`, vc.id AS campaign_id, vc.v_date AS campaign_date FROM `vaccine_trans` LEFT JOIN gp_clinics ON vaccine_trans.clinic=gp_clinics.id LEFT JOIN vaccine_campaign vc ON vc.outlets=vaccine_trans.outlet_id AND vaccine_trans.v_date >= vc.v_date AND vaccine_trans.v_date < vc.v_date + INTERVAL 1 DAY AND vc.recycle=0 where `vaccine_trans`.`recycle`=0 $option order by vaccine_trans.id DESC $limit";
$result=mysqli_query($conn, $query);
$num = $result ? mysqli_num_rows ($result) : 0;
?>
<div class="header">
    <h1 class="headerH1"><img src='../common/img/vaccine.png'> List of Transactions</h1>
</div>

<div class="idx-panel">
    <!-- Toolbar -->
    <div class="idx-toolbar">
        <div class="idx-actions">
            <a href='vaccine_invoice.php' class="idx-icon-link" data-tooltip='Import Invoice No.ber'><img
                    src='../common/img/plus.png'></a>
            <span class="idx-sep"></span>
            <a href='vaccine_print_option.php' class="idx-icon-link" data-tooltip='Export Referral Letter'><img
                    src='../common/img/print.png'></a>
            <span class="idx-sep"></span>
            <a href='vaccine_calendar.php' class="idx-icon-link" data-tooltip='Calendar'><img
                    src='../common/img/calendar.png'></a>
            <span class="idx-sep"></span>
            <div class="idx-pagination">
                <?php
        if ($pageno == 1) {
            echo "<img src='../common/img/fast_back_grey.png'> <img src='../common/img/backward_grey.png'>";
        } else {
            $prevpage = $pageno - 1;
            echo "<a href='{$_SERVER['PHP_SELF']}?pageno=1&outlet_id=$outlet_id&s=1&key=$key&search_status=$search_status&date_from=$date_from&date_to=$date_to' title='First Page'><img src='../common/img/fast_back.png'></a>";
            echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$prevpage&outlet_id=$outlet_id&s=1&key=$key&search_status=$search_status&date_from=$date_from&date_to=$date_to' title='Previous Page'><img src='../common/img/backward.png'></a>";
        }
        ?>
                <span>( Page</span>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                    <select name="pageno" onchange="submit()"
                        style="border-radius:6px; padding:3px 6px; font-size:13px;">
                        <?php
                $displayPages = max(1, $lastpage);
                for ($i=1; $i<=$displayPages; $i++) {
                    $select = ($pageno == $i) ? 'selected' : '';
                    echo "<option value=$i $select>$i</option>";
                } ?>
                    </select>
                    <input type='hidden' name='key' value='<?php echo $key; ?>' />
                    <input type='hidden' name='s' value='1' />
                    <input type='hidden' name='search_status' value='<?php echo $search_status; ?>' />
                    <input type='hidden' name='outlet_id' value='<?php echo $outlet_id; ?>' />
                    <input type='hidden' name='date_from' value='<?php echo $date_from; ?>' />
                    <input type='hidden' name='date_to' value='<?php echo $date_to; ?>' />
                </form>
                <span>of <?php echo $lastpage; ?> )</span>
                <?php
        if ($pageno >= $lastpage) {
            echo "<img src='../common/img/front_grey.png'> <img src='../common/img/fast_front_grey.png'>";
        } else {
            $nextpage = $pageno + 1;
            echo "<a href='{$_SERVER['PHP_SELF']}?pageno=$nextpage&outlet_id=$outlet_id&s=1&key=$key&search_status=$search_status&date_from=$date_from&date_to=$date_to' title='Following Page'><img src='../common/img/front.png'></a>";
            echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$lastpage&outlet_id=$outlet_id&s=1&key=$key&search_status=$search_status&date_from=$date_from&date_to=$date_to' title='Last Page'><img src='../common/img/fast_front.png'></a>";
        }
        ?>
            </div>
        </div>
        <form id="FormName" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName"
            class="idx-filters">
            <?php
        $e_outlet = explode(",", $outlet);
        echo "<select name='outlet_id' onchange='submit()'>";
        echo "<option value=''>All</option>";
        foreach ($e_outlet as $value) {
            $query2 = "SELECT id, code FROM `outlet` where id='$value' limit 0,1";
            $result2 = mysqli_query($conn, $query2);
            $row2 = $result2 ? $result2->fetch_assoc() : null;
            $id = $row2 ? stripslashes($row2['id'] ?? '') : '';
            $code = $row2 ? stripslashes($row2['code'] ?? '') : '';
            $v = ($outlet_id == $id) ? 'selected' : '';
            echo "<option $v value='$value'>$code</option>";
        }
        echo "</select>";
        ?>
            <select name='search_status' onchange='submit();'>
                <option value='' <?php if ($search_status === '') { echo 'selected'; } ?>>All</option>
                <option value='0' <?php if ($search_status == '0') { echo 'selected'; } ?>>Pending</option>
                <option value='1' <?php if ($search_status == '1') { echo 'selected'; } ?>>Vaccinated</option>
                <option value='2' <?php if ($search_status == '2') { echo 'selected'; } ?>>Referred to Doctor</option>
                <option value='3' <?php if ($search_status == '3') { echo 'selected'; } ?>>Cancelled</option>
            </select>
            <input type="date" name="date_from" value='<?php echo $date_from; ?>' onchange='submit();' />
            <input type="date" name="date_to" value='<?php echo $date_to; ?>' onchange='submit();' />
            <input type="text" name="key" autocomplete="off" placeholder='Customer Name/IC/Inv No' />
            <input type='hidden' name='s' value='1' />
            <a href='vaccine_index.php' class="idx-btn idx-btn-reset">Reset</a>
        </form>
    </div>

    <!-- Row 3: Table -->
    <form id="view_form" name="view_form" action="print.php" method="post" target="Map">
        <table width="100%" class='myTable'>
            <tr>
                <th width='3%'>No.</th>
                <th width='9%'>Campaign Date</th>
                <th width='12%'>Clinic In Charge</th>
                <th width='10%'>Handled By</th>
                <th width='14%'>Customer Details</th>
                <th width='10%'>Vaccine</th>
                <th width='16%'>Remarks</th>
                <th width='8%'>Status</th>
                <th width='10%'>Invoice No.</th>
                <th width='2%'>Edit</th>
            </tr>
            <?php
if ($num > 0 ) {
$i=0;
while ($row = $result->fetch_assoc()) {
$trans_id = stripslashes($row['id'] ?? '');
$timestamp = stripslashes($row['timestamp'] ?? '');
$cust_id = stripslashes($row['cust_id'] ?? '');
	//search for customer name, IC, and phone
	$query3="select `customer_name`, `ic`, `phone`, `language`, `nationality` from `customer` where `id`='$cust_id' limit 0,1";
	$result3 = mysqli_query($conn, $query3);
	$row3 = $result3 ? $result3 -> fetch_assoc() : null;
	$customer_name= $row3 ? stripslashes($row3['customer_name'] ?? '') : '';
	$ic= $row3 ? stripslashes($row3['ic'] ?? '') : '';
	$phone= $row3 ? stripslashes($row3['phone'] ?? '') : '';
	$phone_parts = explode("@",$phone);
	$language= $row3 ? stripslashes($row3['language'] ?? '') : '';
	$nationality= $row3 ? stripslashes($row3['nationality'] ?? '') : '';
	if($language==0){$language_txt='BI';} else if($language==1){$language_txt='BM';} else if($language==2){$language_txt='BC';}
	$hp=preg_replace('/\D/', '', $phone_parts[0]);
	$prefix='60';
	$prefix2='6';
$outlet_id = stripslashes($row['outlet_id'] ?? '');
	//search for outlet code
	$query3="SELECT `code`, `company`, `o_location`, `office1` FROM `outlet` WHERE `id`='$outlet_id' limit 0,1";
	$result3 = mysqli_query($conn, $query3);
	$row3 = $result3 ? $result3 -> fetch_assoc() : null;
	$code= $row3 ? stripslashes($row3['code'] ?? '') : '';
	$company= $row3 ? stripslashes($row3['company'] ?? '') : '';
	$o_location= $row3 ? stripslashes($row3['o_location'] ?? '') : '';
	$outlet_phone= $row3 ? stripslashes($row3['office1'] ?? '') : '';
	$outlet_phone=preg_replace('/\D/', '', $outlet_phone);
$item_code = stripslashes($row['item_code'] ?? '');
	//search for item description
	$query3="SELECT `name` FROM `simple` WHERE `item_code`='$item_code' limit 0,1";
	$result3 = mysqli_query($conn, $query3);
	$row3 = $result3 ? $result3 -> fetch_assoc() : null;
	$description= $row3 ? stripslashes($row3['name'] ?? '') : '';
$batch_num = stripslashes($row['batch_num'] ?? '');
$expiry_date = stripslashes($row['expiry_date'] ?? '');
$remark = stripslashes($row['remark'] ?? '');
$status = stripslashes($row['status'] ?? '');
$inv_num = stripslashes($row['inv_num'] ?? '');
$operator = stripslashes($row['operator'] ?? '');
	//search for staff name
	$query4="SELECT `nama_staff` FROM `staff` WHERE `id`='$operator' limit 0,1";
	$result4 = mysqli_query($conn, $query4);
	$row4 = $result4 ? $result4 -> fetch_assoc() : null;
	$staff_name= $row4 ? stripslashes($row4['nama_staff'] ?? '') : '';
$v_date = stripslashes($row['v_date'] ?? '');
$dateTime = new DateTime($v_date);
$date = $dateTime->format('Y-m-d');
$time = $dateTime->format('h:i A');
$v_date="$date $time";
$clinic = stripslashes($row['name'] ?? '');
$dr_name = stripslashes($row['dr_name'] ?? '');
$campaign_id   = stripslashes($row['campaign_id'] ?? '');
$campaign_date = stripslashes($row['campaign_date'] ?? '');
if(substr("$hp", 0, 2)=='01'){$new="<a href='javascript:void(0);' onclick='whatsapp($prefix2$hp, \"$trans_id\", \"$language\",\"$customer_name\",\"$v_date\",\"$o_location\",\"$clinic\",\"$outlet_phone\");' title='Send Whatsapp'><img src='../common/img/wa.png' width='15px'>$phone</a><br/>";} else if(substr("$hp", 0, 1)=='1'){$new="<a href='javascript:void(0);' onclick='whatsapp($prefix$hp, \"$trans_id\", \"$language\",\"$customer_name\",\"$v_date\",\"$o_location\",\"$clinic\",\"$outlet_phone\");' title='Send Whatsapp'><img src='../common/img/wa.png' width='15px'>$phone</a>";} else {$new="<a href='javascript:void(0);' onclick='whatsapp($hp, \"$trans_id\", \"$language\",\"$customer_name\",\"$v_date\",\"$o_location\",\"$clinic\",\"$outlet_phone\");' title='Send Whatsapp'><img src='../common/img/wa.png' width='15px'>$phone</a><br/>";}


//calculate numbering asc
$r=(($pageno-1)*$rows_per_page)+$i+1;
?>
            <tr>
                <td><?php echo $r; ?>.</td>
                <td>
                    <?php if($campaign_id > 0) { echo "<a href='vaccine_campaign.php?id=$campaign_id' target='_blank'>$campaign_date</a>"; } else { echo "<span style='color:#ccc;'>-</span>"; } ?>
                </td>
                <td><small style="color:#6b7280;">Clinic:</small><br><?php echo $clinic; ?><br><small
                        style="color:#6b7280;">Doctor:</small><br><?php echo $dr_name; ?></td>
                <td title='<?php echo $timestamp; ?>'><?php echo $staff_name; ?><br>(<?php echo $code; ?>)</td>
                <td><a href='../customer/index.php?id=<?php echo $cust_id; ?>'
                        target='_blank'><?php echo "$customer_name<br>$ic<br>$language_txt<br>$nationality</a><br>$new"; ?>
                </td>
                <td><?php echo "$item_code<br>$description"; ?></td>
                <td><?php if($status==0){ ?><textarea rows='4' cols='25' name='remark'
                        onchange='updateData(this.name, this.value, <?php echo $trans_id; ?>);'><?php echo $remark; ?></textarea><?php } else {echo $remark;} ?>
                </td>
                <td>
                    <select id='status<?php echo $trans_id; ?>' name='status'
                        onchange='updateData(this.name, this.value, <?php echo $trans_id; ?>);'>
                        <option value='0' <?php if($status=='0'){echo 'selected';} ?>>Pending</option>
                        <option value='1' <?php if($status=='1'){echo 'selected';} ?>>Vaccinated</option>
                        <option value='2' <?php if($status=='2'){echo 'selected';} ?>>Referred to Doctor</option>
                        <option value='3' <?php if($status=='3'){echo 'selected';} ?>>Cancelled</option>
                    </select>
                </td>
                <td><?php if($status==0){ ?><input id='inv_num_<?php echo $trans_id; ?>' name='inv_num' type='text'
                        onchange='updateData(this.name, this.value, <?php echo $trans_id; ?>);'
                        value='<?php echo $inv_num; ?>' size='15'
                        maxlength='15'><?php } else { echo $inv_num; echo "<input type='hidden' id='inv_num_".$trans_id."' value='".htmlspecialchars($inv_num)."'>"; } ?>
                </td>
                <td align='center'>
                    <?php echo "<a href=\"vaccine_update.php?id=$trans_id\" title = 'Update'><img src='../common/img/edit.png' width='16px'></a><br><a href=\"vaccine_index.php?id=$trans_id&d=1\" title = 'Delete' onclick=\"return confirm('Are you sure to delete this transaction? This action cannot be undone.')\" ><img src='../common/img/trash.png' width='16px'></a><br>"; ?>
                    <a href="#" id="clinicBtn_<?php echo $trans_id; ?>" title="Book Alpro Clinic Appointment">
                        <img src="../common/img/clinic_logo.png" width="20px">
                    </a>
                    <script>
                    document.getElementById("clinicBtn_<?php echo $trans_id; ?>").addEventListener("click", function() {
                        openClinicLink(
                            <?php echo json_encode($ic); ?>,
                            <?php echo json_encode($customer_name); ?>,
                            <?php echo json_encode($hp); ?>,
                            document.getElementById("inv_num_<?php echo $trans_id; ?>").value,
                            <?php echo json_encode("(".$code.") ".(isset($comp_arr[$company]) ? $comp_arr[$company] : '')); ?>
                        );
                    });
                    </script>

                    <span id='feedbackdiv<?php echo $trans_id; ?>'></span>
                </td>
            </tr>
            <?php
	$i++;
	}}
	?>
        </table>
    </form>
</div>
<script>
function openClinicLink(ic, name, phone, receiptNo, paymentChannel) {
    let errors = [];

    if (!ic || ic.trim() === "") errors.push("NRIC is missing");
    if (!name || name.trim() === "") errors.push("Customer name is missing");
    if (!phone || phone.trim() === "") errors.push("Mobile number is missing");

    if (errors.length > 0) {
        alert("Please complete the following before proceeding:\n- " + errors.join("\n- "));
        return;
    }

    // The Nexus link is signed server-side so the shared secret stays out of the page.
    const fields = {
        ic: ic,
        name: name,
        phone: phone,
        receipt_no: receiptNo ? receiptNo.trim() : "",
        payment_channel: paymentChannel ? paymentChannel.trim() : ""
    };

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "vaccine_clinic_link.php";
    form.target = "_blank";
    form.acceptCharset = "UTF-8";

    for (const key in fields) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = fields[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
<?php
$connect=0;
include ('../common/index_adv.php');
?>