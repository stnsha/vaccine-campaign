<?php
require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');

$campaign_id = trim(mysqli_real_escape_string($conn, $_REQUEST['id']));

// Load campaign details
$query = "SELECT vc.id, vc.v_date, vc.outlets AS outlet_id, vc.type, vc.status,
          vc.clinic AS clinic_id,
          o.code AS outlet_code, o.company AS outlet_name,
          vcl.name, vcl.dr_name, vcl.phone_1, vcl.address
          FROM vaccine_campaign vc
          LEFT JOIN outlet o ON vc.outlets = o.id
          LEFT JOIN gp_clinics vcl ON vc.clinic = vcl.id
          WHERE vc.id='$campaign_id'";
$result = mysqli_query($conn, $query);

if(!$result) {
    echo "<fieldset class='center'><img src='../common/img/warning.png'><br/><b>Database Error:</b><br/>" . mysqli_error($conn) . "<br/><br/><a href='vaccine_calendar.php'>Back to Calendar</a></fieldset>";
    $connect=0; include('../common/index_adv.php'); exit;
}

$campaign = mysqli_fetch_assoc($result);

if(!$campaign) {
    echo "<fieldset class='center'><img src='../common/img/warning.png'><br/>Campaign not found.<br/><a href='vaccine_calendar.php'>Back to Calendar</a></fieldset>";
    $connect=0; include('../common/index_adv.php'); exit;
}

$v_date      = $campaign['v_date'];
$outlet_id   = $campaign['outlet_id'];
$camp_type   = $campaign['type'];   // 1=HQ, 2=Outlet
$camp_status = $campaign['status']; // 0=Waiting ack, 1=Acknowledged, 2=Cancelled
$days_until  = (strtotime($v_date) - strtotime(date('Y-m-d'))) / 86400;

// Permission flags
$user_outlets    = explode(',', $outlet);
$user_has_access = ($vaccine_autho == '1') || in_array($outlet_id, $user_outlets);

// Edit: HQ can edit HQ campaigns; outlet (or HQ) can edit outlet campaigns
$can_edit = false;
if($camp_status != '2') {
    if($camp_type == '1' && $vaccine_autho == '1') { $can_edit = true; }
    if($camp_type == '2' && $user_has_access)      { $can_edit = true; }
}

// Cancel: same as edit
$can_cancel = ($can_edit && $days_until >= 0);

// Revert: undo a cancellation; mirrors cancel permission
$can_revert = false;
if($camp_status == '2' && $days_until >= 0) {
    if($camp_type == '1' && $vaccine_autho == '1') { $can_revert = true; }
    if($camp_type == '2' && $user_has_access)      { $can_revert = true; }
}

// Acknowledge: outlet staff, HQ campaigns only, status 0→1
$can_acknowledge = ($camp_type == '1' && $user_has_access && $vaccine_autho != '1' && $camp_status == '0' && $days_until >= 0);

// Handle transaction delete
if(isset($_GET['d']) && isset($_GET['trans_id']) && $user_has_access) {
    $del_trans_id = trim(mysqli_real_escape_string($conn, $_GET['trans_id']));
    $qdel = "UPDATE vaccine_trans SET recycle=1 WHERE id='$del_trans_id'";
    mysqli_query($conn, $qdel);
}

// Build company lookup for clinic booking
$comp_query = "SELECT id, company_name FROM finance_expenses_gl";
$comp_result = mysqli_query($conn, $comp_query);
$comp_arr = array();
if($comp_result) {
    while($comp_row = mysqli_fetch_assoc($comp_result)) {
        $comp_arr[$comp_row['id']] = $comp_row['company_name'];
    }
}
$outlet_company  = $campaign['outlet_name'];
$outlet_code_txt = $campaign['outlet_code'];
$payment_channel = '('.$outlet_code_txt.') '.(isset($comp_arr[$outlet_company]) ? $comp_arr[$outlet_company] : '');

// Load transactions for this campaign
$trans_query = "SELECT vt.id, vt.status, vt.remark, vt.inv_num, vt.item_code,
                c.customer_name, c.ic, c.phone,
                s.nama_staff AS operator_name,
                si.name AS item_name
                FROM vaccine_trans vt
                LEFT JOIN customer c ON vt.cust_id = c.id
                LEFT JOIN staff s ON vt.operator = s.id
                LEFT JOIN simple si ON vt.item_code = si.item_code
                WHERE vt.outlet_id = '$outlet_id'
                  AND DATE(vt.v_date) = '$v_date'
                  AND vt.recycle = 0
                ORDER BY vt.id";
$trans_result = mysqli_query($conn, $trans_query);

if(!$trans_result) {
    echo "<fieldset class='center'><img src='../common/img/warning.png'><br/><b>Database Error:</b><br/>" . mysqli_error($conn) . "<br/><br/><a href='vaccine_calendar.php'>Back to Calendar</a></fieldset>";
    $connect=0; include('../common/index_adv.php'); exit;
}

$trans_count      = mysqli_num_rows($trans_result);
$vaccinated_count = 0;
$trans_rows       = array();
while($row = mysqli_fetch_assoc($trans_result)) {
    if($row['status'] == '1') { $vaccinated_count++; }
    $trans_rows[] = $row;
}

$status_label = array('0'=>'Pending','1'=>'Vaccinated','2'=>'Referred to Doctor','3'=>'Cancelled');
$status_color = array('0'=>'#FF6600','1'=>'#008800','2'=>'#0000CC','3'=>'#CC0000');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<style>
.btn {
    border: 2px solid black; background-color: white; color: black;
    padding: 5px 8px; border-radius: 5px; font-size: 12px;
    text-decoration: none; display: inline-block;
}
.btn-blue  { border-color: #2196F3; color: dodgerblue; }
.btn-green { border-color: #4CAF50; color: green; }
.btn-red   { border-color: #f44336; color: red; }
</style>

<div class="header" style="position: relative;">
    <b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
    <h1 class="headerH1"><img src='../common/img/vaccine.png' width='20px'> Vaccine Campaign Details</h1>
    <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<div align='left' style='margin-bottom:8px;'>
    <a href="vaccine_calendar.php">Back to Calendar</a>
    <?php if($can_edit) { ?>
    &nbsp;|&nbsp; <a class="btn btn-blue" href="vaccine_update_campaign.php?id=<?php echo $campaign_id; ?>"><img src="../common/img/edit.png" width="14px"> Edit Campaign</a>
    <?php } ?>
</div>

<?php
if(isset($_GET['updated']) && $_GET['updated'] == '1') {
    echo '<div style="background-color:#EEFFEE; border:2px solid #00AA00; padding:10px; margin:8px 0; border-radius:4px;"><img src="../common/img/tick.png" width="15px"> <b style="color:#008800;">Campaign updated successfully!</b></div>';
}
?>

<fieldset>
    <legend><b>Campaign Information</b></legend>
    <table width="100%" border="0" cellspacing="5" cellpadding="5">
        <tr>
            <td width="20%"><b>Vaccination Date:</b></td>
            <td>
                <?php echo date('d F Y', strtotime($v_date)); ?>
                <?php
                if(round($days_until) == 0) {
                    echo ' &nbsp;<b style="color:#0066CC;">(Today)</b>';
                } else if($days_until > 0) {
                    echo ' &nbsp;<span style="color:#008800;">(in '.round($days_until).' day(s))</span>';
                } else {
                    echo ' &nbsp;<span style="color:#666;">(Completed)</span>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><b>Outlet:</b></td>
            <td><?php echo $campaign['outlet_code'].($campaign['outlet_name'] ? ' - '.$campaign['outlet_name'] : ''); ?></td>
        </tr>
        <tr>
            <td><b>Clinic:</b></td>
            <td><?php echo $campaign['name']; ?></td>
        </tr>
        <tr>
            <td><b>Doctor:</b></td>
            <td><?php echo $campaign['dr_name']; ?></td>
        </tr>
        <tr>
            <td><b>Clinic Contact:</b></td>
            <td><?php echo $campaign['phone_1']; ?></td>
        </tr>
        <tr>
            <td><b>Clinic Address:</b></td>
            <td><?php echo $campaign['address']; ?></td>
        </tr>
        <tr>
            <td><b>Campaign Type:</b></td>
            <td>
                <?php if($camp_type == '1') { ?>
                <span style="background:#3366FF; color:white; padding:2px 8px; border-radius:3px; font-size:11px; font-weight:bold;">HQ</span>
                &nbsp;<b>HQ Initiated Campaign</b>
                <?php } else { ?>
                <span style="background:#FF6600; color:white; padding:2px 8px; border-radius:3px; font-size:11px; font-weight:bold;">Outlet</span>
                &nbsp;<b>Outlet Initiated Campaign</b>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td><b>Status:</b></td>
            <td>
                <?php
                if($camp_status == '2') {
                    // Cancelled
                    echo '<img src="../common/img/bclose.png" width="15px"> <b style="color:#CC0000;">Cancelled</b>';
                    echo '<br/><small style="color:#999;">This campaign has been cancelled.</small>';
                    if($can_revert) {
                        echo '<br/><a href="javascript:void(0);" onclick="revertCampaign()" class="btn btn-green" style="margin-top:5px; font-size:11px;">Revert Cancellation</a>';
                    }

                } else if($camp_type == '1' && $vaccine_autho == '1') {
                    // HQ user, HQ campaign: full status control
                    if($days_until < 0) {
                        echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#666666;">Completed</b>';
                        echo '<br/><small style="color:#999;">Campaign date has passed.</small>';
                    } else {
                ?>
                <select id="status_select" style="padding:5px; width:320px;" onchange="updateStatus()">
                    <option value="0" <?php echo $camp_status == '0' ? 'selected' : ''; ?>>Waiting for Outlet Acknowledgement</option>
                    <option value="1" <?php echo $camp_status == '1' ? 'selected' : ''; ?>>Acknowledged - Recruiting Customers</option>
                    <option value="2" style="color:#CC0000;">Cancel Campaign</option>
                </select>
                <span id="status_update_msg" style="color:#008800; margin-left:10px; display:none;">Updated</span>
                <?php
                    }

                } else if($camp_type == '1' && $user_has_access) {
                    // Outlet staff, HQ campaign: can only acknowledge
                    if($days_until < 0) {
                        echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#666666;">Completed</b>';
                    } else if($camp_status == '0') {
                ?>
                <select id="status_select" style="padding:5px; width:320px;" onchange="updateStatus()">
                    <option value="0" selected>Waiting for Outlet Acknowledgement</option>
                    <option value="1">Acknowledge - Confirm Participation</option>
                </select>
                <span id="status_update_msg" style="color:#008800; margin-left:10px; display:none;">Updated</span>
                <br/><small style="color:#999;">Only HQ can cancel HQ campaigns.</small>
                <?php
                    } else {
                        // Already acknowledged - read only
                        if($days_until > 0) {
                            echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#008800;">Acknowledged - Recruiting Customers</b>';
                        } else if(round($days_until) == 0) {
                            echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#0066CC;">Today is the Vaccine Event</b>';
                        }
                    }

                } else if($camp_type == '2' && $user_has_access) {
                    // Outlet-initiated campaign
                    if($days_until < 0) {
                        echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#666666;">Completed</b>';
                    } else if(round($days_until) == 0) {
                        echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#0066CC;">Today is the Vaccine Event</b>';
                    } else {
                        echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#008800;">Acknowledged - Recruiting Customers</b>';
                    }
                    if($can_cancel) {
                        echo '<br/><a href="javascript:void(0);" onclick="cancelCampaign()" style="color:#CC0000; font-size:11px;">Cancel Campaign</a>';
                    }

                } else {
                    // Read-only view for other users
                    if($camp_status == '0') {
                        echo '<img src="../common/img/personal_exp.png" width="15px"> <b style="color:#FF6600;">Waiting for Outlet Acknowledgement</b>';
                    } else if($camp_status == '1') {
                        if($days_until > 0) {
                            echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#008800;">Acknowledged - Recruiting Customers</b>';
                        } else if(round($days_until) == 0) {
                            echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#0066CC;">Today is the Vaccine Event</b>';
                        } else {
                            echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#666666;">Completed</b>';
                        }
                    }
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><b>Registered Customers:</b></td>
            <td><b><?php echo $trans_count; ?></b> booked</td>
        </tr>
        <tr>
            <td></td>
            <td><a href="vaccine_invoice.php?campaign_date=<?php echo urlencode($v_date); ?>&campaign_id=<?php echo $campaign_id; ?>" style="background:#2e6da4;color:#fff;padding:4px 12px;font-size:12px;border-radius:4px;text-decoration:none;">+ Add Transaction</a></td>
        </tr>
    </table>
</fieldset>

<script>
function updateStatus() {
    var select = document.getElementById('status_select');
    var newStatus = select.value;
    var campaignId = '<?php echo $campaign_id; ?>';

    if(newStatus == '2') {
        if(!confirm('WARNING: Cancelling this campaign cannot be undone.\n\nAre you sure you want to cancel this campaign?')) {
            select.value = '<?php echo $camp_status; ?>';
            return;
        }
    } else if(!confirm('Are you sure you want to change the campaign status?')) {
        select.value = '<?php echo $camp_status; ?>';
        return;
    }

    var msg = document.getElementById('status_update_msg');
    msg.innerHTML = 'Updating...';
    msg.style.display = 'inline';
    msg.style.color = '#FF6600';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'vaccine_ajax_update_campaign.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if(xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if(response.success) {
                msg.innerHTML = 'Updated';
                msg.style.color = '#008800';
                setTimeout(function() { location.reload(); }, 800);
            } else {
                msg.innerHTML = 'Error: ' + response.message;
                msg.style.color = '#AA0000';
                select.value = '<?php echo $camp_status; ?>';
            }
        }
    };
    xhr.send('action=update_status&campaign_id=' + campaignId + '&status=' + newStatus);
}

function cancelCampaign() {
    if(!confirm('WARNING: Cancelling this campaign cannot be undone.\n\nAre you sure?')) { return; }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'vaccine_ajax_update_campaign.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if(xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if(response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }
    };
    xhr.send('action=update_status&campaign_id=<?php echo $campaign_id; ?>&status=2');
}

function revertCampaign() {
    if(!confirm('Are you sure you want to revert this cancellation and restore the campaign?')) { return; }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'vaccine_ajax_update_campaign.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if(xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if(response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }
    };
    xhr.send('action=revert_status&campaign_id=<?php echo $campaign_id; ?>');
}

function checkAll(checkbox, location) {
    location = location.toLowerCase();
    var common = checkbox.parentNode;
    while (common.nodeName.toLowerCase() != location && common != document) {
        common = common.parentNode;
    }
    var inputs = common.getElementsByTagName("input");
    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].type == "checkbox") {
            inputs[i].checked = checkbox.checked;
        }
    }
}

function generatePrint() {
    var form = document.getElementById("print_form");
    window.open("", "VaccinePrint", "status=0,title=0,height=600,width=800,scrollbars=1");
    form.submit();
}

function updateTransStatus(newStatus, transId) {
    var msg = document.getElementById('trans_msg_' + transId);
    msg.innerHTML = 'Saving...';
    msg.style.color = '#FF6600';
    msg.style.display = 'inline';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'vaccine_ajax.php?n=status&v=' + newStatus + '&t_id=' + transId, true);
    xhr.onreadystatechange = function() {
        if(xhr.readyState == 4 && xhr.status == 200) {
            msg.innerHTML = 'Saved';
            msg.style.color = '#008800';
            setTimeout(function() { msg.style.display = 'none'; }, 1500);
        }
    };
    xhr.send();
}
</script>

<fieldset>
    <legend><b>Customer Transactions</b></legend>
    <?php if($trans_count == 0) { ?>
    <p style="color:#999; text-align:center;">No transactions recorded for this campaign yet.</p>
    <?php } else { ?>
    <form id="print_form" method="post" action="vaccine_print_form.php" target="VaccinePrint">
    <table border="0" cellpadding="4" cellspacing="1" bgcolor="#EFEFEF" width="100%" class='myTable' id='transTable'>
        <tr>
            <th width='3%'><input type="checkbox" id="chkAll" onclick="checkAll(this, 'table');" checked /></th>
            <th width='3%'>No.</th>
            <th width='18%'>Customer</th>
            <th width='11%'>IC</th>
            <th width='13%'>Vaccine</th>
            <th width='9%'>Operator</th>
            <th width='11%'>Status</th>
            <th width='17%'>Remark</th>
            <th width='7%'>Edit</th>
        </tr>
        <?php
        $n = 1;
        foreach($trans_rows as $tr) {
            $s_label = isset($status_label[$tr['status']]) ? $status_label[$tr['status']] : $tr['status'];
            $s_color = isset($status_color[$tr['status']]) ? $status_color[$tr['status']] : '#000';
        ?>
        <tr>
            <td align='center'><input type="checkbox" name="bulk_print[]" value="<?php echo $tr['id']; ?>" checked /></td>
            <td align='center'><?php echo $n++; ?>.</td>
            <td><?php echo $tr['customer_name']; ?></td>
            <td align='center'><?php echo $tr['ic']; ?></td>
            <td><?php echo $tr['item_code']; ?><?php if($tr['item_name']) echo '<br/><small>'.$tr['item_name'].'</small>'; ?></td>
            <td align='center'><?php echo $tr['operator_name']; ?></td>
            <td align='center'>
                <?php if($user_has_access) { ?>
                <select onchange="updateTransStatus(this.value, <?php echo $tr['id']; ?>)" style="font-size:11px;">
                    <option value='0' <?php echo $tr['status']=='0' ? 'selected' : ''; ?>>Pending</option>
                    <option value='1' <?php echo $tr['status']=='1' ? 'selected' : ''; ?>>Vaccinated</option>
                    <option value='2' <?php echo $tr['status']=='2' ? 'selected' : ''; ?>>Referred to Doctor</option>
                    <option value='3' <?php echo $tr['status']=='3' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <span id='trans_msg_<?php echo $tr['id']; ?>' style='display:none; font-size:10px;'></span>
                <?php } else { ?>
                <b style="color:<?php echo $s_color; ?>"><?php echo $s_label; ?></b>
                <?php } ?>
            </td>
            <td><?php echo $tr['remark']; ?></td>
            <td align='center'>
                <?php
                $tr_phone_parts = explode('@', isset($tr['phone']) ? $tr['phone'] : '');
                $tr_hp = preg_replace('/\D/', '', $tr_phone_parts[0]);
                $tr_inv = isset($tr['inv_num']) ? $tr['inv_num'] : '';
                ?>
                <a href="vaccine_update.php?id=<?php echo $tr['id']; ?>" title="Edit"><img src='../common/img/edit.png' width='16px'></a><br>
                <?php if($user_has_access) { ?>
                <a href="vaccine_campaign.php?id=<?php echo $campaign_id; ?>&amp;d=1&amp;trans_id=<?php echo $tr['id']; ?>" title="Delete" onclick="return confirm('Are you sure you want to delete this transaction?');"><img src='../common/img/trash.png' width='16px'></a><br>
                <?php } ?>
                <a href="#" id="clinicBtn_<?php echo $tr['id']; ?>" title="Book Alpro Clinic Appointment"><img src='../common/img/clinic_logo.png' width='20px'></a>
                <input type="hidden" id="inv_num_<?php echo $tr['id']; ?>" value="<?php echo htmlspecialchars($tr_inv); ?>">
                <script>
                document.getElementById("clinicBtn_<?php echo $tr['id']; ?>").addEventListener("click", function() {
                    openClinicLink(
                        <?php echo json_encode($tr['ic']); ?>,
                        <?php echo json_encode($tr['customer_name']); ?>,
                        <?php echo json_encode($tr_hp); ?>,
                        document.getElementById("inv_num_<?php echo $tr['id']; ?>").value,
                        <?php echo json_encode($payment_channel); ?>
                    );
                });
                </script>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <td colspan='9' align='center' style='padding:8px;'>
                <select name='print_type'>
                    <option value='1'>Referral Letter</option>
                </select>
                <input type='hidden' name='staff' value='<?php echo $nama_staff; ?>' />
                <input type='hidden' name='position' value='<?php echo $status_semasa; ?>' />
                <input type='button' value='Generate' onclick='generatePrint();' style='padding:4px 14px;cursor:pointer;' />
            </td>
        </tr>
    </table>
    </form>
    <?php } ?>
</fieldset>

<script src="https://cdn.jsdelivr.net/npm/jssha/dist/sha256.js"></script>
<script>
function openClinicLink(ic, name, phone, receiptNo, paymentChannel) {
    var errors = [];
    if (!ic || ic.trim() === "") errors.push("NRIC is missing");
    if (!name || name.trim() === "") errors.push("Customer name is missing");
    if (!phone || phone.trim() === "") errors.push("Mobile number is missing");
    if (errors.length > 0) {
        alert("Please complete the following before proceeding:\n- " + errors.join("\n- "));
        return;
    }
    receiptNo      = receiptNo ? receiptNo.trim() : "";
    paymentChannel = paymentChannel ? paymentChannel.trim() : "";
    var exp      = Math.floor(Date.now() / 30000);
    var comments = "Referral Case from Octopus Module";
    var payload  = name + "|" + ic + "|" + phone + "|" + receiptNo + "|" + paymentChannel + "|" + comments + "|" + exp;
    var secretKey = "octopus2nexus integration";
    var token    = generateSHA256(payload + secretKey);
    var headerData = {
        NRIC: ic,
        PatientName: name,
        MobileNo: phone,
        ReceiptNo: receiptNo,
        PaymentChannel: paymentChannel,
        Comments: comments,
        Exp: exp,
        Token: token
    };
    var headerJson   = JSON.stringify(headerData);
    var headerBase64 = btoa(unescape(encodeURIComponent(headerJson)));
    var url = "http://thenexushealth.com/OctopusBookPublicAppointment?header=" + encodeURIComponent(headerBase64);
    window.open(url, "_blank");
}

function generateSHA256(message) {
    var shaObj = new jsSHA("SHA-256", "TEXT");
    shaObj.update(message);
    return shaObj.getHash("HEX");
}
</script>
<?php
$connect=0;
include('../common/index_adv.php');
?>
