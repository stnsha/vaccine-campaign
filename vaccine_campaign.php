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
    padding: 8px 10px !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    vertical-align: middle !important;
    text-align: left !important;
}
.myTable thead th {
    background: #f1f5f9 !important;
    color: #374151 !important;
    font-weight: bold !important;
    border-bottom: 2px solid #d1d5db !important;
    white-space: nowrap !important;
}
.myTable tbody tr { border-bottom: 1px solid #e5e7eb !important; }
.myTable tbody tr:hover { background: #f8fafc !important; }
.myTable tbody td { color: #111 !important; }
.info-th {
    width: 180px !important;
    font-weight: bold !important;
    background: transparent !important;
    color: inherit !important;
    white-space: nowrap !important;
    border-bottom: 1px solid #e5e7eb !important;
}
.myTable select {
    border-radius: 8px !important;
    padding: 4px 8px !important;
    border: 1px solid #cfcfcf !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    box-sizing: border-box !important;
    background: #fff !important;
}
.myTable select:focus {
    border-color: rgba(0,91,150,.55) !important;
    box-shadow: 0 0 0 3px rgba(0,91,150,.12) !important;
    outline: none !important;
}
button.upd-submit, a.upd-submit, .upd-submit {
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
button.upd-submit:hover, a.upd-submit:hover, .upd-submit:hover { background: #004d80 !important; border-color: #004d80 !important; }
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
a.upd-danger, .upd-danger {
    display: inline-flex !important;
    align-items: center !important;
    background: #e9ecef !important;
    color: #c53030 !important;
    border: 1px solid #f5c6cb !important;
    border-radius: 8px !important;
    height: 32px !important;
    padding: 5px 18px !important;
    font-weight: bold !important;
    text-decoration: none !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px !important;
    box-sizing: border-box !important;
    line-height: 1 !important;
    cursor: pointer !important;
}
a.upd-danger:hover, .upd-danger:hover { background: #fff0f0 !important; border-color: #e53e3e !important; }
a.upd-success, .upd-success {
    display: inline-flex !important;
    align-items: center !important;
    background: #e9ecef !important;
    color: #059669 !important;
    border: 1px solid #a7f3d0 !important;
    border-radius: 8px !important;
    height: 32px !important;
    padding: 5px 18px !important;
    font-weight: bold !important;
    text-decoration: none !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px !important;
    box-sizing: border-box !important;
    line-height: 1 !important;
    cursor: pointer !important;
}
a.upd-success:hover, .upd-success:hover { background: #ecfdf5 !important; border-color: #059669 !important; }
.tbl-action-link {
    display: inline-flex !important;
    align-items: center !important;
    gap: 3px !important;
    font-size: 12px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    color: #005B96 !important;
    text-decoration: none !important;
    padding: 2px 6px !important;
    border-radius: 5px !important;
}
.tbl-action-link:hover { background: #e8f0f7 !important; }
.tbl-del-link {
    display: inline-flex !important;
    align-items: center !important;
    gap: 3px !important;
    font-size: 12px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    color: #c53030 !important;
    text-decoration: none !important;
    padding: 2px 6px !important;
    border-radius: 5px !important;
}
.tbl-del-link:hover { background: #fff0f0 !important; }
.camp-badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 2px 10px !important;
    border-radius: 20px !important;
    font-size: 11px !important;
    font-weight: bold !important;
    color: #fff !important;
}
</style>
<?php
$campaign_id = trim(mysqli_real_escape_string($conn, $_REQUEST['id'] ?? ''));

$query  = "SELECT vc.id, vc.v_date, vc.outlets AS outlet_id, vc.type, vc.status,
           vc.clinic AS clinic_id,
           o.code AS outlet_code, o.company AS outlet_name,
           o.office1 AS outlet_office1, o.office2 AS outlet_office2,
           vcl.name, vcl.dr_name, vcl.phone_1, vcl.address
           FROM vaccine_campaign vc
           LEFT JOIN outlet o ON vc.outlets = o.id
           LEFT JOIN gp_clinics vcl ON vc.clinic = vcl.id
           WHERE vc.id='$campaign_id' AND vc.recycle = 0";
$result = mysqli_query($conn, $query);

if (!$result) { ?>
<div class="idx-panel">
    <p style="color:#c53030 !important;font-size:13px !important;"><b>Database Error:</b> <?php echo mysqli_error($conn); ?></p>
    <a href="vaccine_calendar.php" class="upd-back">Back to Calendar</a>
</div>
<?php
    $connect = 0; include('../common/index_adv.php'); exit;
}

$campaign = mysqli_fetch_assoc($result);

if (!$campaign) { ?>
<div class="idx-panel">
    <p style="color:#c53030 !important;font-size:13px !important;">Campaign not found.</p>
    <a href="vaccine_calendar.php" class="upd-back">Back to Calendar</a>
</div>
<?php
    $connect = 0; include('../common/index_adv.php'); exit;
}

$v_date      = $campaign['v_date'];
$outlet_id   = $campaign['outlet_id'];
$camp_type   = $campaign['type'];
$camp_status = $campaign['status'];
$days_until  = (strtotime($v_date) - strtotime(date('Y-m-d'))) / 86400;

$user_outlets    = explode(',', $outlet);
$user_has_access = ($vaccine_autho == '1') || in_array($outlet_id, $user_outlets);

$can_edit = false;
if ($camp_status != '2') {
    if ($camp_type == '1' && $vaccine_autho == '1') { $can_edit = true; }
    if ($camp_type == '2' && $user_has_access)      { $can_edit = true; }
}
$can_cancel  = ($can_edit && $days_until >= 0);
$can_revert  = ($camp_status == '2' && $days_until >= 0 && $vaccine_autho == '1');
$can_acknowledge = ($camp_type == '1' && $user_has_access && $vaccine_autho != '1' && $camp_status == '0' && $days_until >= 0);

// Build WhatsApp reminder links to the outlet (Send Reminder feature)
$reminder_date = date('d F Y', strtotime($v_date));
$reminder_message = "Hi team, would like to confirm the arrangements for the *{$reminder_date} Vaccination Event*:\n\n"
    . "- Kindly *acknowledge* the vaccination event in the Octopus calendar.\n"
    . "- Update the *GP Clinic* in the calendar.\n\n"
    . "📌 Once updated, kindly send me a *screenshot* of the Octopus calendar as proof of completion.\n\n"
    . "Terima Kasih. Xie Xie Ni.";
$reminder_message_encoded = rawurlencode($reminder_message);

$reminder_links = array();
$reminder_candidates = array($campaign['outlet_office1'], $campaign['outlet_office2']);
foreach ($reminder_candidates as $raw_phone) {
    $contact = preg_replace('/\D/', '', $raw_phone ?? '');
    if ($contact !== '' && substr($contact, 0, 2) == '01') {
        $reminder_links[] = array('display' => $raw_phone, 'number' => '6' . $contact);
    }
}

if (isset($_GET['d']) && isset($_GET['trans_id']) && $user_has_access) {
    $del_trans_id = trim(mysqli_real_escape_string($conn, $_GET['trans_id']));
    mysqli_query($conn, "UPDATE vaccine_trans SET recycle=1 WHERE id='$del_trans_id'");
}

$comp_query  = "SELECT id, company_name FROM finance_expenses_gl";
$comp_result = mysqli_query($conn, $comp_query);
$comp_arr    = array();
if ($comp_result) {
    while ($comp_row = mysqli_fetch_assoc($comp_result)) {
        $comp_arr[$comp_row['id']] = $comp_row['company_name'];
    }
}
$outlet_company  = $campaign['outlet_name'];
$outlet_code_txt = $campaign['outlet_code'];
$payment_channel = '(' . $outlet_code_txt . ') ' . (isset($comp_arr[$outlet_company]) ? $comp_arr[$outlet_company] : '');

$trans_query  = "SELECT vt.id, vt.status, vt.remark, vt.inv_num, vt.item_code,
                 c.customer_name, c.ic, c.phone,
                 s.nama_staff AS operator_name,
                 si.name AS item_name
                 FROM vaccine_trans vt
                 LEFT JOIN customer c ON vt.cust_id = c.id
                 LEFT JOIN staff s ON vt.operator = s.id
                 LEFT JOIN simple si ON vt.item_code = si.item_code
                 WHERE vt.outlet_id = '$outlet_id'
                   AND vt.v_date >= '$v_date' AND vt.v_date < '$v_date' + INTERVAL 1 DAY
                   AND vt.recycle = 0
                 ORDER BY vt.id";
$trans_result = mysqli_query($conn, $trans_query);

if (!$trans_result) { ?>
<div class="idx-panel">
    <p style="color:#c53030 !important;font-size:13px !important;"><b>Database Error:</b> <?php echo mysqli_error($conn); ?></p>
    <a href="vaccine_calendar.php" class="upd-back">Back to Calendar</a>
</div>
<?php
    $connect = 0; include('../common/index_adv.php'); exit;
}

$trans_count      = mysqli_num_rows($trans_result);
$vaccinated_count = 0;
$trans_rows       = array();
while ($row = mysqli_fetch_assoc($trans_result)) {
    if ($row['status'] == '1') { $vaccinated_count++; }
    $trans_rows[] = $row;
}

$status_label = array('0' => 'Pending', '1' => 'Vaccinated', '2' => 'Referred to Doctor', '3' => 'Cancelled');
$status_color = array('0' => '#FF6600', '1' => '#008800', '2' => '#0000CC', '3' => '#CC0000');
?>

<!-- Success flash -->
<?php if (isset($_GET['updated']) && $_GET['updated'] == '1') { ?>
<div style="background:#f0fdf4 !important;border-left:3px solid #059669 !important;border-radius:6px !important;padding:10px 14px !important;margin:6px 0 !important;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;color:#065f46 !important;">
    <img src="../common/img/tick.png" width="15px" /> Campaign updated successfully.
</div>
<?php } ?>

<!-- Campaign Info Panel -->
<div class="idx-panel">
    <!-- Toolbar -->
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap;">
        <a href="vaccine_calendar.php" class="upd-back">Back to Calendar</a>
        <?php if ($can_edit) { ?>
        <a href="vaccine_update_campaign.php?id=<?php echo $campaign_id; ?>" class="upd-submit">
            <img src="../common/img/edit.png" width="13px" style="margin-right:4px;" /> Edit Campaign
        </a>
        <?php } ?>
    </div>

    <table class="myTable">
        <tr>
            <th class="info-th">Vaccination Date</th>
            <td>
                <?php echo date('d F Y', strtotime($v_date)); ?>
                <?php
                if (round($days_until) == 0) {
                    echo ' &nbsp;<b style="color:#005B96;">(Today)</b>';
                } elseif ($days_until > 0) {
                    echo ' &nbsp;<span style="color:#059669;">(in ' . round($days_until) . ' day(s))</span>';
                } else {
                    echo ' &nbsp;<span style="color:#6b7280;">(Completed)</span>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th class="info-th">Outlet</th>
            <td><?php echo htmlspecialchars($campaign['outlet_code'] . ($campaign['outlet_name'] ? ' — ' . $campaign['outlet_name'] : '')); ?></td>
        </tr>
        <tr>
            <th class="info-th">Clinic</th>
            <td><?php echo htmlspecialchars($campaign['name'] ?? ''); ?></td>
        </tr>
        <tr>
            <th class="info-th">Doctor</th>
            <td><?php echo htmlspecialchars($campaign['dr_name'] ?? ''); ?></td>
        </tr>
        <tr>
            <th class="info-th">Clinic Contact</th>
            <td><?php echo htmlspecialchars($campaign['phone_1'] ?? ''); ?></td>
        </tr>
        <tr>
            <th class="info-th">Clinic Address</th>
            <td><?php echo htmlspecialchars($campaign['address'] ?? ''); ?></td>
        </tr>
        <tr>
            <th class="info-th">Campaign Type</th>
            <td>
                <?php if ($camp_type == '1') { ?>
                <span class="camp-badge" style="background:#3366FF !important;">HQ</span>
                &nbsp;<b style="font-size:13px !important;">HQ Initiated Campaign</b>
                <?php } else { ?>
                <span class="camp-badge" style="background:#FF6600 !important;">Outlet</span>
                &nbsp;<b style="font-size:13px !important;">Outlet Initiated Campaign</b>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th class="info-th">Status</th>
            <td>
                <?php
                if ($camp_status == '2') {
                    echo '<img src="../common/img/bclose.png" width="15px"> <b style="color:#CC0000 !important;">Cancelled</b>';
                    echo '<br/><small style="color:#6b7280 !important;font-size:12px !important;">This campaign has been cancelled.</small>';
                    if ($can_revert) {
                        echo '<br/><br/><a href="javascript:void(0);" onclick="revertCampaign()" class="upd-success">Revert Cancellation</a>';
                    }
                } elseif ($camp_type == '1' && $vaccine_autho == '1') {
                    if ($days_until < 0) {
                        echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#6b7280 !important;">Completed</b>';
                        echo '<br/><small style="color:#6b7280 !important;font-size:12px !important;">Campaign date has passed.</small>';
                    } else {
                        echo '<select id="status_select" onchange="updateStatus()" style="min-width:320px;">';
                        echo '<option value="0"' . ($camp_status == '0' ? ' selected' : '') . '>Waiting for Outlet Acknowledgement</option>';
                        echo '<option value="1"' . ($camp_status == '1' ? ' selected' : '') . '>Acknowledged — Recruiting Customers</option>';
                        echo '<option value="2" style="color:#CC0000;">Cancel Campaign</option>';
                        echo '</select>';
                        echo '<span id="status_update_msg" style="color:#008800 !important;margin-left:10px !important;display:none !important;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;">Updated</span>';

                        if ($camp_status == '0') {
                            if (count($reminder_links) > 0) {
                                echo '<div style="margin-top:8px;">';
                                foreach ($reminder_links as $link) {
                                    echo '<a href="https://api.whatsapp.com/send?phone=' . $link['number'] . '&text=' . $reminder_message_encoded . '&language=en" target="_blank" class="upd-success" style="margin-right:8px;">'
                                       . '<img src="../common/img/wa.png" width="15px" style="margin-right:4px;"> Send Reminder (' . htmlspecialchars($link['display']) . ')</a>';
                                }
                                echo '</div>';
                            } else {
                                echo '<br/><small style="color:#6b7280 !important;font-size:12px !important;">No WhatsApp-capable outlet number on file — cannot send reminder.</small>';
                            }
                        }
                    }
                } elseif ($camp_type == '1' && $user_has_access) {
                    if ($days_until < 0) {
                        echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#6b7280 !important;">Completed</b>';
                    } elseif ($camp_status == '0') {
                        echo '<select id="status_select" onchange="updateStatus()" style="min-width:320px;">';
                        echo '<option value="0" selected>Waiting for Outlet Acknowledgement</option>';
                        echo '<option value="1">Acknowledge — Confirm Participation</option>';
                        echo '</select>';
                        echo '<span id="status_update_msg" style="color:#008800 !important;margin-left:10px !important;display:none !important;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;">Updated</span>';
                        echo '<br/><small style="color:#6b7280 !important;font-size:12px !important;">Only HQ can cancel HQ campaigns.</small>';
                    } else {
                        if ($days_until > 0) {
                            echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#059669 !important;">Acknowledged — Recruiting Customers</b>';
                        } elseif (round($days_until) == 0) {
                            echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#005B96 !important;">Today is the Vaccine Event</b>';
                        }
                    }
                } elseif ($camp_type == '2' && $user_has_access) {
                    if ($days_until < 0) {
                        echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#6b7280 !important;">Completed</b>';
                    } elseif (round($days_until) == 0) {
                        echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#005B96 !important;">Today is the Vaccine Event</b>';
                    } else {
                        echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#059669 !important;">Acknowledged — Recruiting Customers</b>';
                    }
                    if ($can_cancel) {
                        echo '<br/><br/><a href="javascript:void(0);" onclick="cancelCampaign()" class="upd-danger">Cancel Campaign</a>';
                    }
                } else {
                    if ($camp_status == '0') {
                        echo '<img src="../common/img/personal_exp.png" width="15px"> <b style="color:#FF6600 !important;">Waiting for Outlet Acknowledgement</b>';
                    } elseif ($camp_status == '1') {
                        if ($days_until > 0) {
                            echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#059669 !important;">Acknowledged — Recruiting Customers</b>';
                        } elseif (round($days_until) == 0) {
                            echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#005B96 !important;">Today is the Vaccine Event</b>';
                        } else {
                            echo '<img src="../common/img/tick.png" width="15px"> <b style="color:#6b7280 !important;">Completed</b>';
                        }
                    }
                }
                ?>
            </td>
        </tr>
        <tr>
            <th class="info-th">Registered Customers</th>
            <td>
                <b style="font-size:13px !important;"><?php echo $trans_count; ?></b> booked
                <?php if ($camp_status != '2') { ?>
                <br/>
                <a href="vaccine_invoice.php?campaign_date=<?php echo urlencode($v_date); ?>&campaign_id=<?php echo $campaign_id; ?>" class="upd-submit" style="margin-top:6px !important;">
                    + Add Transaction
                </a>
                <?php } ?>
            </td>
        </tr>
    </table>
</div>

<!-- Transactions Panel -->
<div class="idx-panel">
    <div style="font-size:14px !important;font-weight:bold !important;font-family:Arial,Helvetica,sans-serif !important;color:#005B96 !important;margin-bottom:12px !important;text-align:left !important;">
        Customer Transactions
    </div>
    <?php if ($trans_count == 0) { ?>
    <p style="color:#6b7280 !important;font-size:13px !important;text-align:center !important;">No transactions recorded for this campaign yet.</p>
    <?php } else { ?>
    <form id="print_form" method="post" action="vaccine_print_form.php" target="VaccinePrint">
        <table class="myTable" id="transTable">
            <thead>
                <tr>
                    <th style="width:3%;text-align:center !important;"><input type="checkbox" id="chkAll" onclick="checkAll(this, 'table');" checked /></th>
                    <th style="width:3%;text-align:center !important;">No.</th>
                    <th style="width:10%;">Invoice No.</th>
                    <th style="width:14%;">Customer</th>
                    <th style="width:11%;">IC</th>
                    <th style="width:13%;">Vaccine</th>
                    <th style="width:9%;">Operator</th>
                    <th style="width:12%;">Status</th>
                    <th style="width:15%;">Remark</th>
                    <th style="width:7%;text-align:center !important;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $n = 1;
            foreach ($trans_rows as $tr) {
                $s_label = isset($status_label[$tr['status']]) ? $status_label[$tr['status']] : $tr['status'];
                $s_color = isset($status_color[$tr['status']]) ? $status_color[$tr['status']] : '#000';
                $tr_phone_parts = explode('@', isset($tr['phone']) ? $tr['phone'] : '');
                $tr_hp  = preg_replace('/\D/', '', $tr_phone_parts[0]);
                $tr_inv = isset($tr['inv_num']) ? $tr['inv_num'] : '';
            ?>
            <tr>
                <td style="text-align:center !important;"><input type="checkbox" name="bulk_print[]" value="<?php echo $tr['id']; ?>" checked /></td>
                <td style="text-align:center !important;"><?php echo $n++; ?></td>
                <td><?php echo htmlspecialchars($tr_inv); ?></td>
                <td><?php echo htmlspecialchars($tr['customer_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($tr['ic'] ?? ''); ?></td>
                <td>
                    <?php echo htmlspecialchars($tr['item_code'] ?? ''); ?>
                    <?php if ($tr['item_name']) { echo '<br/><small style="color:#6b7280 !important;">' . htmlspecialchars($tr['item_name']) . '</small>'; } ?>
                </td>
                <td><?php echo htmlspecialchars($tr['operator_name'] ?? ''); ?></td>
                <td>
                    <?php if ($user_has_access) { ?>
                    <select onchange="updateTransStatus(this.value, <?php echo $tr['id']; ?>)" style="font-size:12px !important;min-width:100px !important;">
                        <option value="0" <?php echo $tr['status'] == '0' ? 'selected' : ''; ?>>Pending</option>
                        <option value="1" <?php echo $tr['status'] == '1' ? 'selected' : ''; ?>>Vaccinated</option>
                        <option value="2" <?php echo $tr['status'] == '2' ? 'selected' : ''; ?>>Referred to Doctor</option>
                        <option value="3" <?php echo $tr['status'] == '3' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <span id="trans_msg_<?php echo $tr['id']; ?>" style="display:none;font-size:11px !important;font-family:Arial,Helvetica,sans-serif !important;"></span>
                    <?php } else { ?>
                    <b style="color:<?php echo $s_color; ?> !important;font-size:13px !important;"><?php echo $s_label; ?></b>
                    <?php } ?>
                </td>
                <td><?php echo htmlspecialchars($tr['remark'] ?? ''); ?></td>
                <td style="text-align:center !important;">
                    <div><a href="vaccine_update.php?id=<?php echo $tr['id']; ?>" class="tbl-action-link" title="Edit">
                        <img src="../common/img/edit.png" width="14px" />
                    </a></div>
                    <?php if ($user_has_access) { ?>
                    <div><a href="vaccine_campaign.php?id=<?php echo $campaign_id; ?>&d=1&trans_id=<?php echo $tr['id']; ?>"
                        class="tbl-del-link" title="Delete"
                        onclick="return confirm('Are you sure you want to delete this transaction?')">
                        <img src="../common/img/trash.png" width="14px" />
                    </a></div>
                    <?php } ?>
                    <div><a href="#" id="clinicBtn_<?php echo $tr['id']; ?>" class="tbl-action-link" title="Book Alpro Clinic Appointment">
                        <img src="../common/img/clinic_logo.png" width="18px" />
                    </a></div>
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
            </tbody>
        </table>
        <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin-top:12px;">
            <select name="print_type" style="border-radius:8px !important;padding:5px 8px !important;border:1px solid #cfcfcf !important;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;height:32px !important;box-sizing:border-box !important;">
                <option value="1">Referral Letter</option>
            </select>
            <input type="hidden" name="staff" value="<?php echo $nama_staff; ?>" />
            <input type="hidden" name="position" value="<?php echo $status_semasa; ?>" />
            <button type="button" onclick="generatePrint();" class="upd-submit">Generate</button>
        </div>
    </form>
    <?php } ?>
</div>

<script>
function updateStatus() {
    var select = document.getElementById('status_select');
    var newStatus = select.value;
    var campaignId = '<?php echo $campaign_id; ?>';
    if (newStatus == '2') {
        if (!confirm('WARNING: Cancelling this campaign cannot be undone.\n\nAre you sure you want to cancel this campaign?')) {
            select.value = '<?php echo $camp_status; ?>';
            return;
        }
    } else if (!confirm('Are you sure you want to change the campaign status?')) {
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
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
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
    if (!confirm('WARNING: Cancelling this campaign cannot be undone.\n\nAre you sure?')) { return; }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'vaccine_ajax_update_campaign.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) { location.reload(); }
            else { alert('Error: ' + response.message); }
        }
    };
    xhr.send('action=update_status&campaign_id=<?php echo $campaign_id; ?>&status=2');
}

function revertCampaign() {
    if (!confirm('Are you sure you want to revert this cancellation and restore the campaign?')) { return; }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'vaccine_ajax_update_campaign.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) { location.reload(); }
            else { alert('Error: ' + response.message); }
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
        if (inputs[i].type == "checkbox") { inputs[i].checked = checkbox.checked; }
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
        if (xhr.readyState == 4 && xhr.status == 200) {
            msg.innerHTML = 'Saved';
            msg.style.color = '#008800';
            setTimeout(function() { msg.style.display = 'none'; }, 1500);
        }
    };
    xhr.send();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/jssha/dist/sha256.js"></script>
<script>
function openClinicLink(ic, name, phone, receiptNo, paymentChannel) {
    var errors = [];
    if (!ic || ic.trim() === "")    { errors.push("NRIC is missing"); }
    if (!name || name.trim() === "") { errors.push("Customer name is missing"); }
    if (!phone || phone.trim() === "") { errors.push("Mobile number is missing"); }
    if (errors.length > 0) {
        alert("Please complete the following before proceeding:\n- " + errors.join("\n- "));
        return;
    }
    receiptNo      = receiptNo ? receiptNo.trim() : "";
    paymentChannel = paymentChannel ? paymentChannel.trim() : "";
    var exp        = Math.floor(Date.now() / 30000);
    var comments   = "Referral Case from Octopus Module";
    var payload    = name + "|" + ic + "|" + phone + "|" + receiptNo + "|" + paymentChannel + "|" + comments + "|" + exp;
    var secretKey  = "octopus2nexus integration";
    var token      = generateSHA256(payload + secretKey);
    var headerData = { NRIC: ic, PatientName: name, MobileNo: phone, ReceiptNo: receiptNo, PaymentChannel: paymentChannel, Comments: comments, Exp: exp, Token: token };
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
$connect = 0;
include('../common/index_adv.php');
?>
