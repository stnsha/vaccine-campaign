<?php
require_once('../lock_adv.php');
$connect = 1;
include('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');
?>
<style type="text/css">
.idx-panel {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,.08);
    padding: 14px 18px;
    margin: 6px 0 10px;
}
.myTable {
    width: 100% !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    border-collapse: collapse !important;
}
.myTable th, .myTable td {
    padding: 6px 10px !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    vertical-align: middle !important;
    text-align: left !important;
}
.filter-th {
    background: #f1f5f9 !important;
    color: #374151 !important;
    font-weight: bold !important;
    text-align: right !important;
    white-space: nowrap !important;
    width: 180px !important;
    border-bottom: 1px solid #e5e7eb !important;
}
.myTable thead th {
    background: #005B96 !important;
    color: #fff !important;
    font-weight: bold !important;
    text-align: center !important;
    border-bottom: 2px solid #004d80 !important;
}
.myTable tbody tr {
    border-bottom: 1px solid #e5e7eb !important;
}
.myTable tbody tr:hover {
    background: #f8fafc !important;
}
.myTable tbody td {
    vertical-align: top !important;
    padding-top: 8px !important;
    padding-bottom: 8px !important;
}
.myTable input[type="text"],
.myTable select {
    border-radius: 8px !important;
    padding: 5px 8px !important;
    border: 1px solid #cfcfcf !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    box-sizing: border-box !important;
    background: #fff !important;
}
.myTable input[type="text"]:focus,
.myTable select:focus {
    border-color: rgba(0,91,150,.55) !important;
    box-shadow: 0 0 0 3px rgba(0,91,150,.12) !important;
    outline: none !important;
}
button.upd-submit, .upd-submit {
    display: inline-flex !important;
    align-items: center !important;
    background: #005B96 !important;
    color: #fff !important;
    border: 1px solid #005B96 !important;
    border-radius: 8px !important;
    height: 32px !important;
    padding: 5px 18px !important;
    font-weight: bold !important;
    cursor: pointer !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px !important;
    box-sizing: border-box !important;
    text-decoration: none !important;
    line-height: 1 !important;
}
button.upd-submit:hover, .upd-submit:hover { background: #004d80 !important; border-color: #004d80 !important; }
a.upd-back, .upd-back {
    display: inline-flex !important;
    align-items: center !important;
    background: #e9ecef !important;
    color: #111 !important;
    border: 1px solid #d0d7de !important;
    border-radius: 8px !important;
    height: 32px !important;
    padding: 5px 18px !important;
    font-weight: bold !important;
    text-decoration: none !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px !important;
    box-sizing: border-box !important;
    line-height: 1 !important;
}
a.upd-back:hover, .upd-back:hover { background: #d8dde3 !important; border-color: #b0b8c1 !important; }
</style>
<link rel="stylesheet" href="../common/css/jquery-ui.css" type="text/css">
<script src="../common/js/jquery-1.5.1.js"></script>
<script src="../common/js/jquery.ui.core.js"></script>
<script src="../common/js/jquery.ui.datepicker.js"></script>
<script>
$(function() {
    $("#date_start").datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true});
    $("#date_end").datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true});
});

function checkAll(checkbox, location) {
    location = location.toLowerCase();
    var CommonLocation = checkbox.parentNode;
    while (CommonLocation.nodeName.toLowerCase() != location && CommonLocation != document) {
        CommonLocation = CommonLocation.parentNode;
    }
    var inputs = CommonLocation.getElementsByTagName("input");
    for (var i = 0; inputs[i]; i++) {
        if (inputs[i].type == "checkbox") {
            inputs[i].checked = checkbox.checked;
        }
    }
}

function view_my_report() {
    var mapForm = document.getElementById("view_form");
    map = window.open("", "Map", "status=0,title=0,height=600,width=800,scrollbars=1");
    if (map) {
        mapForm.submit();
    } else {
        alert('You must allow popups for this map to work.');
    }
}
</script>
<?php
$date_start = trim(mysqli_real_escape_string($conn, $_REQUEST['s']));
$date_end   = trim(mysqli_real_escape_string($conn, $_REQUEST['e']));
$outlet_id  = trim(mysqli_real_escape_string($conn, $_REQUEST['o']));
$status     = trim(mysqli_real_escape_string($conn, $_REQUEST['status']));
if ($status || $status == 0) { $option2 = "and `vaccine_trans`.`status`='$status'"; }
$type = trim(mysqli_real_escape_string($conn, $_REQUEST['type']));

if ($vaccine_autho == '1') {
    $query3  = "select id from outlet where recycle='0' order by code";
    $result3 = mysqli_query($conn, $query3);
    $num3    = mysqli_num_rows($result3);
    if ($num3 > 0) {
        $i3     = 0;
        $outlet = '';
        while ($row3 = $result3->fetch_assoc()) {
            $o_id = stripslashes($row3['id']);
            if ($i3 == '0') { $outlet .= "$o_id"; } else { $outlet .= ",$o_id"; }
            ++$i3;
        }
    }
}
?>

<!-- Filter panel -->
<div class="idx-panel">
    <form name="form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <table class="myTable">
            <tr>
                <th class="filter-th">Vaccination Date Between</th>
                <td>
                    <input id="date_start" name="s" type="text" maxlength="10"
                        value="<?php echo $date_start; ?>" autocomplete="off" onchange="submit();"
                        placeholder="Start date" style="width:120px;" />
                    <span style="font-size:13px !important; margin:0 6px;">and</span>
                    <input id="date_end" name="e" type="text" maxlength="10"
                        value="<?php echo $date_end; ?>" autocomplete="off" onchange="submit();"
                        placeholder="End date" style="width:120px;" />
                </td>
            </tr>
            <tr>
                <th class="filter-th">Outlet</th>
                <td>
                    <?php
                    $e_outlet = explode(",", $outlet);
                    echo "<select name='o' onchange='submit()' style='min-width:160px;'>";
                    echo "<option value=''>All</option>";
                    foreach ($e_outlet as $value) {
                        $query2  = "SELECT id, code FROM `outlet` where id='$value' limit 0,1";
                        $result2 = mysqli_query($conn, $query2);
                        $row2    = $result2->fetch_assoc();
                        @$id   = stripslashes($row2['id']);
                        @$code = stripslashes($row2['code']);
                        if ($outlet_id == $id) { $v = "selected"; } else { $v = ""; }
                        echo "<option $v value='$value'>$code</option>";
                    }
                    echo "</select>";
                    ?>
                </td>
            </tr>
            <tr>
                <th class="filter-th">Status</th>
                <td>
                    <select name="status" onchange="submit();" style="min-width:160px;height:34px;">
                        <option value="0" <?php if ($status == '0') { echo 'selected'; } ?>>Pending</option>
                    </select>
                </td>
            </tr>
        </table>
    </form>
</div>

<!-- Data table panel -->
<div class="idx-panel">
    <form id="view_form" name="view_form" method="post" action="vaccine_print_form.php" target="Map">
        <table class="myTable" id="myTable">
            <thead>
                <tr>
                    <th style="width:3%;text-align:center !important;">
                        <input type="checkbox" id="chkAll" name="chkAll" onclick="checkAll(this, 'table');" checked />
                    </th>
                    <th style="width:4%;text-align:center !important;">No.</th>
                    <th style="width:10%;text-align:center !important;">Vaccination Date</th>
                    <th style="width:18%;">Customer / IC</th>
                    <th style="width:12%;">Vaccine</th>
                    <th style="width:13%;">Handled By</th>
                    <th style="width:14%;">Clinic In Charge</th>
                    <th style="width:15%;">Remark</th>
                    <th style="width:10%;text-align:center !important;">Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($outlet_id) {
                $option = "and `vaccine_trans`.`outlet_id`='$outlet_id'";
            } else {
                $option = "and `vaccine_trans`.`outlet_id` in ($outlet)";
            }
            $query  = "SELECT `vaccine_trans`.`id`, `vaccine_trans`.`timestamp`, `vaccine_trans`.`cust_id`, `vaccine_trans`.`item_code`, `vaccine_trans`.`remark`, `vaccine_trans`.`status`, `vaccine_trans`.`operator`, `vaccine_trans`.`v_date`, `vaccine_trans`.`outlet_id`, `gp_clinics`.`name`, `gp_clinics`.`dr_name` FROM `vaccine_trans` left join gp_clinics on vaccine_trans.clinic=gp_clinics.id where `vaccine_trans`.`recycle`=0 and `vaccine_trans`.`v_date` between '$date_start 00:00:00' and '$date_end 23:59:59' $option $option2 order by vaccine_trans.v_date, vaccine_trans.outlet_id, vaccine_trans.cust_id";
            $result = mysqli_query($conn, $query);
            $num    = mysqli_num_rows($result);
            if ($num > 0) {
                $i = 0;
                while ($row = $result->fetch_assoc()) {
                    $trans_id  = stripslashes($row['id']);
                    $timestamp = stripslashes($row['timestamp']);
                    $cust_id   = stripslashes($row['cust_id']);

                    $query3  = "select `customer_name`, `ic`, `phone` from `customer` where `id`='$cust_id' limit 0,1";
                    $result3 = mysqli_query($conn, $query3);
                    $row3    = $result3->fetch_assoc();
                    @$customer_name = stripslashes($row3["customer_name"]);
                    @$ic            = stripslashes($row3["ic"]);
                    @$phone         = stripslashes($row3["phone"]);
                    $hp      = preg_replace('/\D/', '', $phone);
                    $prefix2 = '6';
                    if (substr("$hp", 0, 2) == '01' || substr("$hp", 0, 1) == '1') {
                        $new = "<a href='javascript:void(0);' onclick=\"window.open(&quot;https://api.whatsapp.com/send?phone=$prefix2$hp&text=Dear%20$dr_name,%0A%0A&language=en&quot;,&quot;Ratting&quot;,&quot;width=1,height=1,left=1,top=1,toolbar=no,scrollbars=no,menubar=no,resizable=no&quot;);\" title='Send Whatsapp'><img src='../common/img/wa.png' width='15px'> " . htmlspecialchars($hp) . "</a>";
                    } else {
                        $new = htmlspecialchars($hp);
                    }

                    $outlet_id_row = stripslashes($row['outlet_id']);
                    $query3  = "SELECT `code` FROM `outlet` WHERE `id`='$outlet_id_row' limit 0,1";
                    $result3 = mysqli_query($conn, $query3);
                    $row3    = $result3->fetch_assoc();
                    @$code = stripslashes($row3["code"]);

                    $item_code = stripslashes($row['item_code']);
                    $query3    = "SELECT `name` FROM `simple` WHERE `item_code`='$item_code' limit 0,1";
                    $result3   = mysqli_query($conn, $query3);
                    $row3      = $result3->fetch_assoc();
                    @$description = stripslashes($row3["name"]);

                    $remark   = stripslashes($row['remark']);
                    $status   = stripslashes($row['status']);
                    $operator = stripslashes($row['operator']);
                    $query4   = "SELECT `nama_staff` FROM `staff` WHERE `id`='$operator' limit 0,1";
                    $result4  = mysqli_query($conn, $query4);
                    $row4     = $result4->fetch_assoc();
                    @$staff_name = stripslashes($row4["nama_staff"]);

                    $v_date  = stripslashes($row['v_date']);
                    $clinic  = stripslashes($row['name']);
                    $dr_name = stripslashes($row['dr_name']);

                    if ($status == 0)      { $status_label = "Pending"; }
                    elseif ($status == 1)  { $status_label = "Vaccinated"; }
                    elseif ($status == 2)  { $status_label = "Referred to Doctor"; }
                    elseif ($status == 3)  { $status_label = "Cancelled"; }
                    else                   { $status_label = $status; }

                    $r = $i + 1;
                    ?>
                    <tr>
                        <td style="text-align:center !important;">
                            <input name="bulk_print[]" value="<?php echo $trans_id; ?>" type="checkbox" checked />
                        </td>
                        <td style="text-align:center !important;"><?php echo $r; ?></td>
                        <td style="text-align:center !important;"><?php echo htmlspecialchars($v_date); ?></td>
                        <td><?php echo htmlspecialchars($customer_name); ?><br /><?php echo htmlspecialchars($ic); ?><br /><?php echo $new; ?></td>
                        <td><?php echo htmlspecialchars($item_code); ?><br /><small style="color:#6b7280;"><?php echo htmlspecialchars($description); ?></small></td>
                        <td title="<?php echo htmlspecialchars($timestamp); ?>"><?php echo htmlspecialchars($staff_name); ?><br /><small style="color:#6b7280;">(<?php echo htmlspecialchars($code); ?>)</small></td>
                        <td><?php echo htmlspecialchars($clinic); ?><br /><small style="color:#6b7280;"><?php echo htmlspecialchars($dr_name); ?></small></td>
                        <td><?php echo htmlspecialchars($remark); ?></td>
                        <td style="text-align:center !important;"><?php echo $status_label; ?></td>
                    </tr>
                    <?php
                    $i++;
                }
            }
            ?>
            </tbody>
        </table>
        <div style="display:flex;align-items:center;gap:10px;margin-top:12px;">
            <select name="print_type" style="border-radius:8px !important;padding:5px 8px !important;border:1px solid #cfcfcf !important;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;height:32px !important;box-sizing:border-box !important;">
                <option value="1" <?php if ($type == '1') { echo 'selected'; } ?>>Referral Letter</option>
            </select>
            <input type="hidden" name="staff" value="<?php echo $nama_staff; ?>" />
            <input type="hidden" name="position" value="<?php echo $status_semasa; ?>" />
            <button type="submit" name="submit" onclick="view_my_report(); return false;" class="upd-submit">Generate</button>
            <a href="vaccine_index.php" class="upd-back">Back</a>
        </div>
    </form>
</div>
<?php
$connect = 0;
include('../common/index_adv.php');
?>
