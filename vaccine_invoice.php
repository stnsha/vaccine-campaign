<?php
require_once('../lock_adv.php');
require_once(__DIR__ . '/vaccine_env.php');
$connect = 1;
include('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');
ini_set('memory_limit', '2000M');
ini_set('max_execution_time', 30000);
?>
<style type="text/css">
.idx-panel {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
    padding: 14px 18px;
    margin: 6px 0 10px;
}

.myTable {
    width: 100%;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    border-collapse: collapse;
}

.myTable th,
.myTable td {
    padding: 6px 10px !important;
    vertical-align: middle !important;
    font-size: 13px !important;
}

.myTable th {
    font-weight: normal !important;
    text-align: left !important;
    white-space: nowrap !important;
    background: transparent !important;
    color: inherit !important;
}

.myTable td {
    text-align: left !important;
}

.myTable input[type="text"],
.myTable input[type="date"],
.myTable input[type="time"],
.myTable input[type="email"],
.myTable select,
.myTable textarea {
    border-radius: 8px !important;
    padding: 5px 8px !important;
    border: 1px solid #cfcfcf !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    box-sizing: border-box !important;
}

.myTable input[type="text"]:focus,
.myTable input[type="date"]:focus,
.myTable input[type="email"]:focus,
.myTable select:focus,
.myTable textarea:focus {
    border-color: rgba(0, 91, 150, .55) !important;
    box-shadow: 0 0 0 3px rgba(0, 91, 150, .12) !important;
    outline: none !important;
}

button.upd-submit,
.upd-submit {
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

button.upd-submit:hover,
.upd-submit:hover {
    background: #004d80 !important;
    border-color: #004d80 !important;
}

a.upd-back,
.upd-back {
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

a.upd-back:hover,
.upd-back:hover {
    background: #d8dde3 !important;
    border-color: #b0b8c1 !important;
}

button.inv-action-btn,
.inv-action-btn {
    display: inline-flex !important;
    align-items: center !important;
    background: #005B96 !important;
    color: #fff !important;
    border: 1px solid #005B96 !important;
    border-radius: 8px !important;
    height: 32px !important;
    padding: 5px 18px !important;
    font-size: 13px !important;
    font-weight: bold !important;
    cursor: pointer !important;
    font-family: Arial, Helvetica, sans-serif !important;
    box-sizing: border-box !important;
    line-height: 1 !important;
}

button.inv-action-btn:hover,
.inv-action-btn:hover {
    background: #004d80 !important;
    border-color: #004d80 !important;
}

a.inv-new-cust-link,
.inv-new-cust-link {
    display: inline-flex !important;
    align-items: center !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-weight: bold !important;
    color: #fff !important;
    background: #0891b2 !important;
    border: 1px solid #0891b2 !important;
    padding: 5px 18px !important;
    height: 32px !important;
    border-radius: 8px !important;
    text-decoration: none !important;
    cursor: pointer !important;
    box-sizing: border-box !important;
    line-height: 1 !important;
}

a.inv-new-cust-link:hover,
.inv-new-cust-link:hover {
    background: #0e7490 !important;
    border-color: #0e7490 !important;
}

.myTable thead th {
    background: #f1f5f9 !important;
    border-bottom: 2px solid #d1d5db !important;
}

.myTable tbody tr {
    border-bottom: 1px solid #e5e7eb !important;
}

.myTable tbody td {
    vertical-align: top !important;
    padding-top: 10px !important;
    padding-bottom: 10px !important;
}

.cust-form-table {
    border: none !important;
    background: none !important;
    width: 100% !important;
    font-size: 13px !important;
}

.cust-form-table td {
    font-size: 13px !important;
    padding: 3px 6px !important;
    vertical-align: middle !important;
}

.cust-form-table td:first-child {
    width: 130px !important;
    font-weight: bold !important;
    white-space: nowrap !important;
}

.cust-form-table input[type="text"],
.cust-form-table input[type="email"],
.cust-form-table input[type="date"],
.cust-form-table select,
.cust-form-table textarea {
    font-size: 13px !important;
    border-radius: 8px !important;
    padding: 5px 8px !important;
    border: 1px solid #cfcfcf !important;
    box-sizing: border-box !important;
    background: #fff !important;
}

.cust-form-table input[type="text"]:focus,
.cust-form-table input[type="email"]:focus,
.cust-form-table input[type="date"]:focus,
.cust-form-table select:focus,
.cust-form-table textarea:focus {
    border-color: rgba(0, 91, 150, .55) !important;
    box-shadow: 0 0 0 3px rgba(0, 91, 150, .12) !important;
    outline: none !important;
}
</style>
<?php

if (isset($_REQUEST['submit'])) {
?>
<script type="text/javascript" src="../common/js/jquery-1.5.1.js"></script>
<script type='text/javascript' src="../common/js/jquery.autocomplete.js"></script>
<link rel="stylesheet" type="text/css" href="../common/css/jquery.autocomplete.css" />
<?php

    function callAPI($method, $url, $data = false)
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "appid: " . vaccine_env('XILNEX_APP_ID'),
            "token: " . vaccine_env('XILNEX_TOKEN'),
            "auth: " . vaccine_env('XILNEX_AUTH')
        ));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CAINFO, vaccine_env('XILNEX_CA_PATH'));
        $result = curl_exec($curl);
        $err = curl_error($curl);
        if ($err) { echo "cURL Error: " . htmlspecialchars($err) . "<br/>"; }
        curl_close($curl);
        return $result;
    }

    // Load all vaccine item codes with their vaccine type info
    $vc_query = "SELECT vc.item_code, vc.vaccine_type, vt.vaccine_name, s.name as item_name FROM vaccine_code vc JOIN vaccine_type vt ON vc.vaccine_type = vt.id LEFT JOIN simple s ON vc.item_code = s.item_code";
    $vc_result = mysqli_query($conn, $vc_query);
    $vaccine_codes = array();
    if ($vc_result) {
        while ($vc_row = $vc_result->fetch_assoc()) {
            $vaccine_codes[$vc_row['item_code']] = array(
                'vaccine_type' => $vc_row['vaccine_type'],
                'vaccine_name' => $vc_row['vaccine_name'],
                'item_name'    => $vc_row['item_name']
            );
        }
    }

    // Build outlet list for transaction forms
    $staff_q = "SELECT outlet FROM staff WHERE id = $id_user";
    $staff_r = mysqli_query($conn, $staff_q);
    $staff_row = $staff_r ? $staff_r->fetch_assoc() : null;
    $outlet_list = $staff_row ? ($staff_row['outlet'] ?? '') : '';
    if ($vaccine_autho == '1') {
        $all_q = "SELECT id FROM outlet WHERE recycle='0' AND code NOT LIKE 'NEC%' AND code NOT LIKE 'NHQ%' AND code NOT LIKE '%0' AND code NOT LIKE '%C' AND code NOT LIKE '%HQ' AND code NOT LIKE 'NSDW%' ORDER BY code";
        $all_r = mysqli_query($conn, $all_q);
        $ids = array();
        if ($all_r) {
            while ($o = $all_r->fetch_assoc()) { $ids[] = $o['id']; }
        }
        $outlet_list = implode(',', $ids);
    }
    $outlet_ids = explode(',', $outlet_list);
    $outlet_options_html = '<option value="">Pick One</option>';
    foreach ($outlet_ids as $oid) {
        $oid = (int)$oid;
        $oc_r = mysqli_query($conn, "SELECT id, code FROM outlet WHERE id='$oid' LIMIT 1");
        $oc = $oc_r ? $oc_r->fetch_assoc() : null;
        if ($oc) {
            $outlet_options_html .= "<option value='" . $oc['id'] . "'>" . htmlspecialchars($oc['code']) . "</option>";
        }
    }

    // Build vaccine type options for transaction forms
    $vt_q = "SELECT id, vaccine_name FROM vaccine_type WHERE recycle = 0";
    $vt_r = mysqli_query($conn, $vt_q);
    $vaccine_type_rows = array();
    if ($vt_r) {
        while ($vt = $vt_r->fetch_assoc()) { $vaccine_type_rows[] = $vt; }
    }

    $race_opts_html = '<option value="">Pick One</option>';
    $rq_all = "SELECT id, ethnic FROM mydna_ethnicities ORDER BY ethnic";
    $rr_all = mysqli_query($conn, $rq_all);
    if ($rr_all) {
        while ($rrow_all = $rr_all->fetch_assoc()) {
            $race_opts_html .= "<option value='" . $rrow_all['id'] . "'>" . htmlspecialchars($rrow_all['ethnic']) . "</option>";
        }
    }

    $reset_url = htmlspecialchars($_SERVER['PHP_SELF']);

    $inv = trim($_REQUEST['id'] ?? '');
    $prefill_campaign_date = isset($_REQUEST['campaign_date']) ? trim($_REQUEST['campaign_date']) : '';
    $prefill_campaign_id   = isset($_REQUEST['campaign_id'])   ? (int)$_REQUEST['campaign_id']   : 0;
    $lines = preg_split('/\r\n|\r|\n/', $inv);

    $form_index = 0;
    $has_any_rows = false;

    // Count existing active records per (inv_num, item_code) to support partial re-import
    $inv_item_counts = array();
    $clean_lines = array();
    foreach ($lines as $l) {
        $l = trim($l);
        if ($l) $clean_lines[] = $l;
    }
    if (!empty($clean_lines)) {
        $esc_list = array();
        foreach ($clean_lines as $ci) {
            $esc_list[] = "'" . mysqli_real_escape_string($conn, $ci) . "'";
        }
        $cnt_q = "SELECT inv_num, item_code, COUNT(*) as cnt FROM vaccine_trans WHERE inv_num IN (" . implode(',', $esc_list) . ") AND recycle = 0 GROUP BY inv_num, item_code";
        $cnt_r = mysqli_query($conn, $cnt_q);
        if ($cnt_r) {
            while ($cnt_row = $cnt_r->fetch_assoc()) {
                if (!isset($inv_item_counts[$cnt_row['inv_num']])) {
                    $inv_item_counts[$cnt_row['inv_num']] = array();
                }
                $inv_item_counts[$cnt_row['inv_num']][$cnt_row['item_code']] = (int)$cnt_row['cnt'];
            }
        }
    }
    ?>
<div class="header" style="position:relative;">
    <b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
    <h1 class="headerH1"
        style="display:flex;justify-content:space-between;align-items:center;width:100%;box-sizing:border-box;"><span
            style="display:flex;align-items:center;gap:6px;font-size:18px;"><img src='../common/img/vaccine.png'
                height='18px'> Vaccine Invoice Import</span><a href='<?php echo $reset_url; ?>'
            style='font-size:13px;font-weight:normal;padding:4px 12px;background:#e8a800;color:#fff;border-radius:8px;text-decoration:none;white-space:nowrap;'>&#8592;
            Enter New Invoice</a></h1>
    <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<form action="vaccine_invoice_save.php" method="post" onsubmit="return validateBeforeSubmit()" novalidate>
    <input type="hidden" name="campaign_id" value="<?php echo $prefill_campaign_id; ?>" />
    <?php
    foreach ($lines as $invoice) {
        $invoice = trim($invoice);
        if (!$invoice) continue;


        $get_data = callAPI('GET', "https://api.xilnex.com/logic/v2/sales/$invoice", "", '');
        $response = json_decode($get_data, true);

        if (strpos($response['status'] ?? '', 'Invalid') !== false) {
            echo "<p><font color='red'><b>Invoice $invoice: Invalid ID / Transaction not yet synced to Xilnex Cloud</b></font></p>";
            continue;
        }

        $client_name    = ucwords(strtolower($response['data']['sale']['client']['name'] ?? ''));
        $client_contact = $response['data']['sale']['client']['contactNo'] ?? '';
        // client_name and client_contact are displayed in the summary table only

        $raw_dt       = substr($response['data']['sale']['dateTime'] ?? '', 0, -5);
        $inv_datetime = str_replace('T', ' ', $raw_dt);
        $inv_date     = substr($raw_dt, 0, 10);
        $inv_time     = substr($raw_dt, 11, 5);
        $items        = $response['data']['sale']['items'] ?? null;

        // Look up outlet id from invoice outlet code
        $outlet_code = mysqli_real_escape_string($conn, $response['data']['sale']['outlet'] ?? '');
        $outq = "SELECT id FROM outlet WHERE code='$outlet_code' AND recycle=0 LIMIT 1";
        $outr = mysqli_query($conn, $outq);
        $outrow = $outr ? $outr->fetch_assoc() : null;
        $prefill_outlet_id = $outrow ? (int)$outrow['id'] : 0;

        $inv_form_id = 'txform_' . preg_replace('/[^a-z0-9]/i', '_', $invoice);
        $vdate_prefill = ($prefill_campaign_date !== '') ? $prefill_campaign_date : $inv_date;
        ?>
    <div class="header" style="position:relative;margin-top:10px;">
        <b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
        <h1 class="headerH1"><img src='../common/img/vaccine.png' height='18px'> Transaction Form &mdash; Invoice
            <?php echo htmlspecialchars($invoice); ?></h1>
        <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
    </div>
    <div class="idx-panel">
        <table width='100%' class='myTable'>
            <tr>
                <th>Client Name</th>
                <td colspan='3'><?php echo htmlspecialchars($client_name); ?></td>
                <th>Contact</th>
                <td colspan='3'><?php echo htmlspecialchars($client_contact); ?></td>
            </tr>
            <tr>
                <th>Invoice Date</th>
                <td colspan='7'><?php echo htmlspecialchars($inv_datetime); ?></td>
            </tr>
            <tr>
                <th>Invoice No</th>
                <td colspan='7'><?php echo htmlspecialchars($invoice); ?></td>
            </tr>
            <tr>
                <th>Item Code</th>
                <th>Description</th>
                <th>Qty</th>
                <th></th>
                <th>Price</th>
                <th></th>
                <th>Total</th>
                <th>Profit</th>
            </tr>
            <?php

        $qualifying_items = array();

        if (is_array($items)) {
            foreach ($items as $itemData) {
                $total = 0;
                $total = ($itemData['quantity'] ?? 0) * ($itemData['unitPrice'] ?? 0);
                $item_code_xi = $itemData['itemCode'] ?? '';
                $is_vaccine = isset($vaccine_codes[$item_code_xi]);
                if (!$is_vaccine) continue;
                echo "<tr style='background:#dff0d8;font-weight:bold;'><td>" . htmlspecialchars($item_code_xi) . "</td><td align='left'>" . htmlspecialchars($itemData['itemName'] ?? '') . "</td><td>" . htmlspecialchars($itemData['quantity'] ?? '') . "</td><td>x</td><td>RM " . number_format($itemData['unitPrice'] ?? 0, 2) . "</td><td>=</td><td>RM " . number_format($total, 2) . "</td><td>RM " . number_format($itemData['profit'] ?? 0, 2) . "</td></tr>";
                if ($is_vaccine) {
                    $qualifying_items[] = array(
                        'item_code'    => $item_code_xi,
                        'qty'          => (int)($itemData['quantity'] ?? 0),
                        'vaccine_type' => $vaccine_codes[$item_code_xi]['vaccine_type'],
                        'vaccine_name' => $vaccine_codes[$item_code_xi]['vaccine_name'],
                        'item_name'    => $vaccine_codes[$item_code_xi]['item_name']
                    );
                }
            }
        } else {
            echo "<tr><td colspan='9'><font color='red'>No response from Xilnex API.</font></td></tr>";
        }
        ?>
        </table>
    </div>
    <?php

        if (empty($qualifying_items)) {
            echo "<p><font color='orange'>No vaccine items found in invoice <b>" . htmlspecialchars($invoice) . "</b>.</font></p>";
            continue;
        }

        // Build flat list of rows: one per remaining (not yet imported) unit per qualifying item
        $rows = array();
        $all_fully_imported = true;
        $partial_info = array();

        foreach ($qualifying_items as $qitem) {
            $qty = $qitem['qty'];
            if ($qty <= 0) continue;
            $existing = isset($inv_item_counts[$invoice][$qitem['item_code']])
                ? $inv_item_counts[$invoice][$qitem['item_code']] : 0;
            $remaining = $qty - $existing;
            if ($remaining <= 0) {
                $partial_info[] = htmlspecialchars($qitem['item_code']) . ': ' . $qty . '/' . $qty . ' already imported';
                continue;
            }
            if ($existing > 0) {
                $partial_info[] = htmlspecialchars($qitem['item_code']) . ': ' . $existing . ' of ' . $qty . ' already imported, ' . $remaining . ' slot(s) remaining';
            }
            $all_fully_imported = false;
            for ($i = 1; $i <= $remaining; $i++) {
                $form_index++;
                $rows[] = array(
                    'rid'          => $form_index,
                    'item_code'    => $qitem['item_code'],
                    'item_name'    => $qitem['item_name'],
                    'vaccine_name' => $qitem['vaccine_name']
                );
            }
        }

        if ($all_fully_imported && !empty($qualifying_items)) {
            echo "<p><font color='red'><b>Invoice " . htmlspecialchars($invoice) . ": Already fully imported.</b></font></p>";
            continue;
        }

        if (!empty($partial_info)) {
            echo "<p><font color='orange'><b>Note for invoice " . htmlspecialchars($invoice) . ": " . implode('; ', $partial_info) . ".</b></font></p>";
        }

        $unique_items = array();
        foreach ($qualifying_items as $qi) {
            if (!isset($unique_items[$qi['item_code']])) {
                $unique_items[$qi['item_code']] = $qi['item_name'] ? $qi['item_name'] : $qi['vaccine_name'];
            }
        }
        ?>
    <?php $has_any_rows = true; ?>
    <div class="idx-panel">
        <table width="100%" class='myTable' style="background:#f8f9fa;">
            <tr>
                <th style="text-align:right;">Outlet</th>
                <td><b><?php echo htmlspecialchars($outlet_code); ?></b></td>
            </tr>
            <tr>
                <th style="text-align:right;">Item Code</th>
                <td>
                    <?php foreach ($unique_items as $code => $name) { ?>
                    <b><?php echo htmlspecialchars($code); ?></b><?php if ($name) echo ' &mdash; ' . htmlspecialchars($name); ?><br />
                    <?php } ?>
                </td>
            </tr>
        </table>
        <table width="100%" class="myTable" style="margin-top:6px;">
            <colgroup>
                <col style="width:4%;" />
                <col style="width:13%;" />
                <col style="width:20%;" />
                <col style="width:52%;" />
                <col style="width:11%;" />
            </colgroup>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Campaign Date <span style="color:red;">*</span></th>
                    <th>Clinic <span style="color:red;">*</span></th>
                    <th>Customer / Contact <span style="color:red;">*</span></th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) { $rid = $row['rid']; ?>
                <tr>
                    <td style="text-align:center !important;">
                        <?php echo $rid; ?>
                        <input type="hidden" id="outlet_<?php echo $rid; ?>" name="rows[<?php echo $rid; ?>][outlet_id]"
                            value="<?php echo $prefill_outlet_id; ?>" />
                        <input type="hidden" name="rows[<?php echo $rid; ?>][inv_num]"
                            value="<?php echo htmlspecialchars($invoice); ?>" />
                        <input type="hidden" name="rows[<?php echo $rid; ?>][item_code]"
                            value="<?php echo htmlspecialchars($row['item_code']); ?>" />
                    </td>
                    <td>
                        <input type="date" id="vdate_<?php echo $rid; ?>" name="rows[<?php echo $rid; ?>][v_date]"
                            value="<?php echo htmlspecialchars($vdate_prefill); ?>" style="width:100%;"
                            onchange="checkCampDate(<?php echo $rid; ?>, '<?php echo $inv_form_id; ?>')" required />
                        <span id="camp_status_<?php echo $rid; ?>"
                            style="font-size:13px;display:block;margin-top:4px;"></span>
                    </td>
                    <td>
                        <input type="text" id="clinic_<?php echo $rid; ?>" name="rows[<?php echo $rid; ?>][clinic]"
                            placeholder="Clinic Name" style="width:100%;" autocomplete="off" required
                            onchange="checkCampDate(<?php echo $rid; ?>, '<?php echo $inv_form_id; ?>')" />
                    </td>
                    <td style="vertical-align:top;">
                        <input id="ic_input_<?php echo $rid; ?>" type="text" placeholder="IC / Name" style="width:100%;"
                            autocomplete="off" />
                        <span id="ic_tick_<?php echo $rid; ?>" style="display:none;color:green;font-weight:bold;"
                            title="Customer updated">&#10003;</span>
                        <div id="cust_panel_<?php echo $rid; ?>"
                            style="display:none;margin-top:5px;font-size:13px !important;border-left:3px solid #005B96;background:#f4f8ff;padding:6px;">
                            <div style="color:#005B96;font-weight:bold;margin-bottom:4px;font-size:13px !important;">
                                Customer found &mdash; review &amp; confirm:</div>
                            <div id="ef_confirmed_<?php echo $rid; ?>"
                                style="display:none;color:green;margin-bottom:4px;"></div>
                            <table class="cust-form-table">
                                <tr>
                                    <td>Name <font color="red">*</font> :</td>
                                    <td><input id="ef_name_<?php echo $rid; ?>" type="text" maxlength="100"
                                            style="width:100%;" /></td>
                                </tr>
                                <tr>
                                    <td>Phone :</td>
                                    <td>
                                        <input id="ef_phone_vis_<?php echo $rid; ?>" type="text" maxlength="12"
                                            placeholder="Phone" />@
                                        <select id="ef_child_vis_<?php echo $rid; ?>">
                                            <option value=""></option>
                                            <?php for ($x = 1; $x <= 10; $x++) { echo "<option value='$x'>$x</option>"; } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Language :</td>
                                    <td>
                                        <select id="ef_lang_<?php echo $rid; ?>">
                                            <option value="0">BI</option>
                                            <option value="1">BM</option>
                                            <option value="2">BC</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Race <font color="red">*</font> :</td>
                                    <td>
                                        <select id="ef_race_<?php echo $rid; ?>" style="width:100%;">
                                            <?php echo $race_opts_html; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Nationality <font color="red">*</font> :</td>
                                    <td>
                                        <select id="ef_nat_sel_<?php echo $rid; ?>" style="width:100%;"
                                            onchange="toggleNationality(this, 'ef_nat_other_<?php echo $rid; ?>')">
                                            <option value="">Pick One</option>
                                            <option value="MALAYSIA">MALAYSIA</option>
                                            <option value="SINGAPORE">SINGAPORE</option>
                                            <option value="INDONESIA">INDONESIA</option>
                                            <option value="BRUNEI">BRUNEI</option>
                                            <option value="PHILIPPINES">PHILIPPINES</option>
                                            <option value="THAILAND">THAILAND</option>
                                            <option value="OTHERS">OTHERS</option>
                                        </select>
                                        <input id="ef_nat_other_<?php echo $rid; ?>" type="text" maxlength="100"
                                            style="display:none;width:100%;" placeholder="Specify" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Email :</td>
                                    <td><input id="ef_email_<?php echo $rid; ?>" type="email" maxlength="50"
                                            style="width:100%;" /></td>
                                </tr>
                                <tr>
                                    <td>Address :</td>
                                    <td><textarea id="ef_addr_<?php echo $rid; ?>" rows="2"
                                            style="width:100%;"></textarea></td>
                                </tr>
                            </table>
                            <div id="ef_msg_<?php echo $rid; ?>" style="margin-top:4px;"></div>
                            <div style="display:flex;align-items:center;gap:10px;margin-top:6px;">
                                <input type="button" value="Update" onclick="saveEditInline(<?php echo $rid; ?>)"
                                    class="inv-action-btn" />
                                <a href="javascript:void(0)" onclick="cancelEditInline(<?php echo $rid; ?>)"
                                    class="upd-back">Cancel</a>
                            </div>
                        </div>
                        <div id="cust_notfound_<?php echo $rid; ?>"
                            style="display:none;margin-top:3px;font-size:13px;color:red;">Not found.</div>
                        <input id="cust_ic_<?php echo $rid; ?>" name="rows[<?php echo $rid; ?>][cust_ic]" type="hidden"
                            value="" />
                        <input id="customer_name_<?php echo $rid; ?>" name="rows[<?php echo $rid; ?>][customer_name]"
                            type="hidden" value="" />
                        <input id="phone2_<?php echo $rid; ?>" name="rows[<?php echo $rid; ?>][phone2]" type="hidden"
                            value="" />
                        <input id="child_num_<?php echo $rid; ?>" name="rows[<?php echo $rid; ?>][child_num]"
                            type="hidden" value="" />
                        <div id="new_cust_btn_<?php echo $rid; ?>" style="margin-top:4px;">
                            <a href="javascript:void(0)" onclick="openNewCustInline(<?php echo $rid; ?>)"
                                class="inv-new-cust-link">+ New Customer</a>
                        </div>
                        <div id="new_cust_panel_<?php echo $rid; ?>"
                            style="display:none;margin-top:6px;font-size:13px !important;border-left:3px solid #888;background:#f9f9f9;padding:6px;">
                            <table class="cust-form-table">
                                <tr>
                                    <td>IC <font color="red">*</font> :</td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;">
                                            <label
                                                style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:13px !important;">
                                                <input type="radio" name="ic_type_<?php echo $rid; ?>" value="new"
                                                    checked onchange="toggleICType(<?php echo $rid; ?>)"> New IC
                                            </label>
                                            <label
                                                style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:13px !important;">
                                                <input type="radio" name="ic_type_<?php echo $rid; ?>" value="old"
                                                    onchange="toggleICType(<?php echo $rid; ?>)"> Old IC/Passport
                                            </label>
                                        </div>
                                        <div id="new_ic_panel_<?php echo $rid; ?>"
                                            style="display:flex;align-items:center;gap:4px;">
                                            <input id="nc_ic1_<?php echo $rid; ?>" type="text" maxlength="6"
                                                style="width:78px;" placeholder="YYMMDD" />
                                            <span>-</span>
                                            <input id="nc_ic2_<?php echo $rid; ?>" type="text" maxlength="2"
                                                style="width:40px;" placeholder="PB" />
                                            <span>-</span>
                                            <input id="nc_ic3_<?php echo $rid; ?>" type="text" maxlength="4"
                                                style="width:60px;" placeholder="XXXX" />
                                        </div>
                                        <div id="old_ic_panel_<?php echo $rid; ?>" style="display:none;">
                                            <div style="margin-bottom:4px;">
                                                <input id="nc_ic_old_<?php echo $rid; ?>" type="text" maxlength="30"
                                                    style="width:100%;" placeholder="IC / Passport No" />
                                            </div>
                                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                                <span style="font-size:13px !important;white-space:nowrap;">Birth
                                                    :</span>
                                                <input id="nc_birth_<?php echo $rid; ?>" type="date" style="flex:1;" />
                                            </div>
                                            <div style="display:flex;align-items:center;gap:8px;">
                                                <span style="font-size:13px !important;white-space:nowrap;">Gender
                                                    :</span>
                                                <label
                                                    style="display:flex;align-items:center;gap:4px;font-size:13px !important;cursor:pointer;">
                                                    <input type="radio" name="nc_gender_<?php echo $rid; ?>"
                                                        value="Male"> Male
                                                </label>
                                                <label
                                                    style="display:flex;align-items:center;gap:4px;font-size:13px !important;cursor:pointer;">
                                                    <input type="radio" name="nc_gender_<?php echo $rid; ?>"
                                                        value="Female"> Female
                                                </label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Name <font color="red">*</font> :</td>
                                    <td><input id="nc_name_<?php echo $rid; ?>" type="text" maxlength="100"
                                            style="width:100%;" /></td>
                                </tr>
                                <tr>
                                    <td>Phone <font color="red">*</font> :</td>
                                    <td>
                                        <input id="nc_phone_<?php echo $rid; ?>" type="text" maxlength="12"
                                            placeholder="Phone" />@
                                        <select id="nc_child_<?php echo $rid; ?>">
                                            <option value=""></option>
                                            <?php for ($x = 1; $x <= 10; $x++) { echo "<option value='$x'>$x</option>"; } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Language :</td>
                                    <td>
                                        <select id="nc_lang_<?php echo $rid; ?>">
                                            <option value="0">BI</option>
                                            <option value="1">BM</option>
                                            <option value="2">BC</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Race <font color="red">*</font> :</td>
                                    <td>
                                        <select id="nc_race_<?php echo $rid; ?>" style="width:100%;">
                                            <?php echo $race_opts_html; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Nationality <font color="red">*</font> :</td>
                                    <td>
                                        <select id="nc_nat_sel_<?php echo $rid; ?>" style="width:100%;"
                                            onchange="toggleNationality(this, 'nc_nat_other_<?php echo $rid; ?>')">
                                            <option value="">Pick One</option>
                                            <option value="MALAYSIA">MALAYSIA</option>
                                            <option value="SINGAPORE">SINGAPORE</option>
                                            <option value="INDONESIA">INDONESIA</option>
                                            <option value="BRUNEI">BRUNEI</option>
                                            <option value="PHILIPPINES">PHILIPPINES</option>
                                            <option value="THAILAND">THAILAND</option>
                                            <option value="OTHERS">OTHERS</option>
                                        </select>
                                        <input id="nc_nat_other_<?php echo $rid; ?>" type="text" maxlength="100"
                                            style="display:none;width:100%;" placeholder="Specify" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Email :</td>
                                    <td><input id="nc_email_<?php echo $rid; ?>" type="email" maxlength="50"
                                            style="width:100%;" /></td>
                                </tr>
                                <tr>
                                    <td>Address :</td>
                                    <td><textarea id="nc_addr_<?php echo $rid; ?>" rows="2"
                                            style="width:100%;"></textarea></td>
                                </tr>
                            </table>
                            <div id="nc_msg_<?php echo $rid; ?>" style="margin-top:4px;"></div>
                            <div style="display:flex;align-items:center;gap:10px;margin-top:6px;">
                                <input type="button" value="Save Customer"
                                    onclick="saveNewCustInline(<?php echo $rid; ?>)" class="inv-action-btn" />
                                <a href="javascript:void(0)" onclick="closeNewCustInline(<?php echo $rid; ?>)"
                                    class="upd-back">Cancel</a>
                            </div>
                        </div>
                    </td>
                    <td>
                        <textarea name="rows[<?php echo $rid; ?>][remark]" rows="3" style="width:100%;"></textarea>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function() {
        <?php foreach ($rows as $row) { $rid = $row['rid']; ?>
        jQuery("#ic_input_<?php echo $rid; ?>").autocomplete("vaccine_autoComplete2.php", {
            width: 450,
            matchContains: true,
            selectFirst: false,
            minChars: 3,
            delay: 400
        });
        jQuery("#ic_input_<?php echo $rid; ?>").result(function() {
            var rid = <?php echo $rid; ?>;
            var ic = document.getElementById("ic_input_" + rid).value.split(':')[0].trim();
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        populateEditPanel(rid, data);
                        document.getElementById("cust_panel_" + rid).style.display = 'block';
                        document.getElementById("cust_notfound_" + rid).style.display = 'none';
                        document.getElementById("new_cust_btn_" + rid).style.display = 'none';
                        document.getElementById("new_cust_panel_" + rid).style.display = 'none';
                    }
                }
            };
            xhr.open("GET", "vaccine_customer_fetch.php?ic=" + encodeURIComponent(ic), true);
            xhr.send();
        });
        jQuery("#ic_input_<?php echo $rid; ?>").blur(function() {
            var panelDiv = document.getElementById("cust_panel_" + <?php echo $rid; ?>);
            if (panelDiv && panelDiv.style.display !== 'none') {
                return;
            }
            lookupIC(<?php echo $rid; ?>);
        });
        jQuery("#clinic_<?php echo $rid; ?>").autocomplete("vaccine_autoComplete.php", {
            width: 400,
            matchContains: true,
            selectFirst: false,
            delay: 10
        });
        jQuery("#clinic_<?php echo $rid; ?>").result(function() {
            checkCampDate(<?php echo $rid; ?>, '<?php echo $inv_form_id; ?>');
        });
        // Check campaign on load for pre-filled date
        checkCampDate(<?php echo $rid; ?>, '<?php echo $inv_form_id; ?>');
        <?php } ?>
    });

    function lookupIC(rid) {
        var ic = document.getElementById("ic_input_" + rid).value.replace(/\D/g, '');
        var panelDiv = document.getElementById("cust_panel_" + rid);
        var notFoundDiv = document.getElementById("cust_notfound_" + rid);
        var icHidden = document.getElementById("cust_ic_" + rid);
        var nameHidden = document.getElementById("customer_name_" + rid);
        if (ic.length < 12) {
            panelDiv.style.display = 'none';
            notFoundDiv.style.display = 'none';
            icHidden.value = '';
            nameHidden.value = '';
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    populateEditPanel(rid, data);
                    panelDiv.style.display = 'block';
                    notFoundDiv.style.display = 'none';
                } else {
                    panelDiv.style.display = 'none';
                    notFoundDiv.style.display = 'block';
                    icHidden.value = '';
                    nameHidden.value = '';
                }
            }
        };
        xhr.open("GET", "vaccine_customer_fetch.php?ic=" + encodeURIComponent(ic), true);
        xhr.send();
    }

    function checkCampDate(rid, formId) {
        var dateEl = document.getElementById("vdate_" + rid);
        var date = dateEl ? dateEl.value : '';
        var outletEl = document.getElementById("outlet_" + rid);
        var outlet = outletEl ? outletEl.value : '';
        var span = document.getElementById("camp_status_" + rid);
        var clinicVal = document.getElementById("clinic_" + rid) ? document.getElementById("clinic_" + rid).value : '';
        var clinicId = parseInt(clinicVal.split(':')[0].trim()) || 0;
        if (!date || !outlet) {
            span.innerHTML = '';
            return;
        }
        var url = "vaccine_ajax_check_campaign.php?outlet_id=" + encodeURIComponent(outlet) + "&v_date=" +
            encodeURIComponent(date);
        if (clinicId > 0) {
            url += "&clinic_id=" + clinicId;
        }
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r.found) {
                        span.innerHTML = '<a href="vaccine_campaign.php?id=' + r.id +
                            '" target="_blank" style="color:green;">Campaign found</a>';
                        var clinicField = document.getElementById("clinic_" + rid);
                        if (clinicField && clinicField.value.trim() === '' && r.clinic_id) {
                            clinicField.value = r.clinic_id + ': ' + r.clinic + ' (' + r.dr_name + ')';
                        }
                    } else if (r.multiple) {
                        span.innerHTML = '<font color="orange">' + r.count +
                            ' campaigns on this date. Select a clinic.</font>';
                    } else {
                        if (clinicId > 0) {
                            span.innerHTML =
                                '<font color="red">Does not exist.</font> <a href="javascript:void(0)" onclick="createCampaign(' +
                                rid + ',\'' + formId + '\')" style="color:#e8a800;cursor:pointer;">Create?</a>';
                        } else {
                            span.innerHTML =
                                '<font color="red">Does not exist. Select clinic first to create.</font>';
                        }
                    }
                } catch (e) {}
            }
        };
        xhr.open("GET", url, true);
        xhr.send();
    }

    function createCampaign(rid, formId) {
        var dateEl2 = document.getElementById("vdate_" + rid);
        var date = dateEl2 ? dateEl2.value : '';
        var outletEl = document.getElementById("outlet_" + rid);
        var outlet = outletEl ? outletEl.value : '';
        var clinicVal = document.getElementById("clinic_" + rid).value;
        var clinicId = clinicVal.split(':')[0].trim();
        var span = document.getElementById("camp_status_" + rid);

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
                        span.innerHTML = '<a href="vaccine_campaign.php?id=' + r.id +
                            '" target="_blank" style="color:green;">Campaign created</a>';
                    } else {
                        span.innerHTML = '<font color="red">' + r.error + '</font>';
                    }
                } catch (e) {
                    span.innerHTML = '<font color="red">Unexpected error.</font>';
                }
            }
        };
        xhr.open("POST", "vaccine_campaign_save.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send("outlet_id=" + encodeURIComponent(outlet) + "&v_date=" + encodeURIComponent(date) + "&clinic_id=" +
            encodeURIComponent(clinicId));
    }
    </script>
    <?php
    }
    if ($has_any_rows) { ?>
    <div style="margin:10px 0;">
        <button type="submit" name="submit" class="upd-submit">Submit All</button>
        <div id="form_error_msg"
            style="display:none;margin-top:8px;padding:8px 12px;background:#fff5f5;border-left:3px solid #e53e3e;border-radius:6px;color:#c53030;font-weight:bold;font-size:13px;text-align:left !important;">
            </div>
    </div>
    <?php } ?>
</form>
<script type="text/javascript">
function toggleNationality(select, otherId) {
    var other = document.getElementById(otherId);
    if (select.value === 'OTHERS') {
        other.style.display = '';
    } else {
        other.style.display = 'none';
        other.value = '';
    }
}

function populateEditPanel(rid, data) {
    var tick = document.getElementById("ic_tick_" + rid);
    if (tick) {
        tick.style.display = 'none';
    }
    document.getElementById("ic_input_" + rid).value = data.ic + ' : ' + data.name;
    document.getElementById("ef_name_" + rid).value = data.name;
    document.getElementById("ef_phone_vis_" + rid).value = data.phone;
    document.getElementById("ef_child_vis_" + rid).value = data.child_num;
    document.getElementById("ef_lang_" + rid).value = data.language;
    document.getElementById("ef_race_" + rid).value = data.race;
    document.getElementById("ef_email_" + rid).value = data.email;
    document.getElementById("ef_addr_" + rid).value = data.addr;
    document.getElementById("cust_ic_" + rid).value = data.ic;
    document.getElementById("customer_name_" + rid).value = data.name;
    document.getElementById("phone2_" + rid).value = data.phone;
    document.getElementById("child_num_" + rid).value = data.child_num;
    var natSel = document.getElementById("ef_nat_sel_" + rid);
    var natOther = document.getElementById("ef_nat_other_" + rid);
    var predefined = ['MALAYSIA', 'SINGAPORE', 'INDONESIA', 'BRUNEI', 'PHILIPPINES', 'THAILAND'];
    var nat = data.nationality;
    if (nat && predefined.indexOf(nat) === -1) {
        natSel.value = 'OTHERS';
        natOther.style.display = '';
        natOther.value = nat;
    } else {
        natSel.value = nat ? nat : '';
        natOther.style.display = 'none';
        natOther.value = '';
    }
    document.getElementById("ef_confirmed_" + rid).style.display = 'none';
    document.getElementById("ef_confirmed_" + rid).innerHTML = '';
    document.getElementById("ef_msg_" + rid).innerHTML = '';
}

function saveEditInline(rid) {
    var name = document.getElementById("ef_name_" + rid).value.trim();
    var ic = document.getElementById("cust_ic_" + rid).value.trim();
    var phone = document.getElementById("ef_phone_vis_" + rid).value.trim();
    var child = document.getElementById("ef_child_vis_" + rid).value;
    var language = document.getElementById("ef_lang_" + rid).value;
    var race = document.getElementById("ef_race_" + rid).value;
    var email = document.getElementById("ef_email_" + rid).value.trim();
    var addr = document.getElementById("ef_addr_" + rid).value.trim();
    var natSel = document.getElementById("ef_nat_sel_" + rid);
    var natOther = document.getElementById("ef_nat_other_" + rid);
    var msgEl = document.getElementById("ef_msg_" + rid);

    if (!name || !race || !natSel.value) {
        msgEl.innerHTML = "<font color='red'>Please fill in all required fields.</font>";
        return;
    }
    var nationality;
    if (natSel.value === 'OTHERS') {
        if (natOther.value.trim() === '') {
            msgEl.innerHTML = "<font color='red'>Please specify nationality.</font>";
            return;
        }
        nationality = natOther.value.toUpperCase();
    } else {
        nationality = natSel.value;
    }
    var params = "ic=" + encodeURIComponent(ic) +
        "&name=" + encodeURIComponent(name) +
        "&phone=" + encodeURIComponent(phone) +
        "&child_num=" + encodeURIComponent(child) +
        "&language=" + encodeURIComponent(language) +
        "&race=" + encodeURIComponent(race) +
        "&nationality=" + encodeURIComponent(nationality) +
        "&email=" + encodeURIComponent(email) +
        "&addr=" + encodeURIComponent(addr);
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var res = JSON.parse(xhr.responseText);
            if (res.success) {
                document.getElementById("customer_name_" + rid).value = res.name;
                document.getElementById("phone2_" + rid).value = phone;
                document.getElementById("child_num_" + rid).value = child;
                document.getElementById("cust_panel_" + rid).style.display = 'none';
                var tick = document.getElementById("ic_tick_" + rid);
                if (tick) {
                    tick.style.display = '';
                }
                msgEl.innerHTML = '';
            } else {
                msgEl.innerHTML = "<font color='red'>" + res.error + "</font>";
            }
        }
    };
    xhr.open("POST", "vaccine_customer_update_save.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send(params);
}

function openNewCustInline(rid) {
    document.getElementById("new_cust_panel_" + rid).style.display = 'block';
    document.getElementById("nc_msg_" + rid).innerHTML = '';
    var ic = document.getElementById("ic_input_" + rid).value.replace(/\D/g, '');
    if (ic.length === 12) {
        var el1 = document.getElementById("nc_ic1_" + rid);
        var el2 = document.getElementById("nc_ic2_" + rid);
        var el3 = document.getElementById("nc_ic3_" + rid);
        if (el1) el1.value = ic.substring(0, 6);
        if (el2) el2.value = ic.substring(6, 8);
        if (el3) el3.value = ic.substring(8, 12);
    }
}

function closeNewCustInline(rid) {
    document.getElementById("new_cust_panel_" + rid).style.display = 'none';
}

function cancelEditInline(rid) {
    document.getElementById("cust_panel_" + rid).style.display = 'none';
    document.getElementById("new_cust_btn_" + rid).style.display = 'block';
    var icInput = document.getElementById("ic_input_" + rid);
    if (icInput) {
        icInput.value = '';
    }
    var tick = document.getElementById("ic_tick_" + rid);
    if (tick) {
        tick.style.display = 'none';
    }
    document.getElementById("cust_ic_" + rid).value = '';
    document.getElementById("customer_name_" + rid).value = '';
    document.getElementById("phone2_" + rid).value = '';
    document.getElementById("child_num_" + rid).value = '';
}

function toggleICType(rid) {
    var radios = document.getElementsByName("ic_type_" + rid);
    var val = 'new';
    for (var i = 0; i < radios.length; i++) {
        if (radios[i].checked) {
            val = radios[i].value;
            break;
        }
    }
    document.getElementById("new_ic_panel_" + rid).style.display = (val === 'new') ? 'flex' : 'none';
    document.getElementById("old_ic_panel_" + rid).style.display = (val === 'old') ? 'block' : 'none';
}

function markInvalid(el, invalid) {
    if (el) {
        el.style.borderColor = invalid ? '#e53e3e' : '';
    }
}

function fieldValue(id) {
    var el = document.getElementById(id);
    return el ? el.value.trim() : '';
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function rowLabel(rid) {
    var name = fieldValue('customer_name_' + rid) || fieldValue('ef_name_' + rid) || fieldValue('nc_name_' + rid);
    if (!name) {
        var typed = fieldValue('ic_input_' + rid);
        if (typed.indexOf(':') !== -1) {
            name = typed.split(':').slice(1).join(':').trim();
        }
    }
    return 'Row ' + rid + (name ? ' (' + name + ')' : '');
}

function validateBeforeSubmit() {
    var firstOffender = null;
    var errors = [];
    var dateInputs = document.querySelectorAll('[id^="vdate_"]');
    for (var k = 0; k < dateInputs.length; k++) {
        var rid = dateInputs[k].id.replace('vdate_', '');
        var label = rowLabel(rid);
        var rowErrors = [];
        var clinicEl = document.getElementById('clinic_' + rid);
        var icInputEl = document.getElementById('ic_input_' + rid);
        var editPanel = document.getElementById('cust_panel_' + rid);
        var newPanel = document.getElementById('new_cust_panel_' + rid);
        var editOpen = editPanel && editPanel.style.display !== 'none';
        var newOpen = newPanel && newPanel.style.display !== 'none';
        var offender = null;

        var dateBad = dateInputs[k].value === '';
        if (dateBad) {
            rowErrors.push('Campaign date is required.');
            offender = offender || dateInputs[k];
        }

        var clinicValue = clinicEl ? clinicEl.value.trim() : '';
        var clinicBad = (parseInt(clinicValue.split(':')[0].trim()) || 0) === 0;
        if (clinicBad) {
            rowErrors.push(clinicValue === '' ? 'Clinic is required.' : 'Clinic must be selected from the dropdown list.');
            offender = offender || clinicEl;
        }

        if (editOpen) {
            if (!fieldValue('ef_name_' + rid)) {
                rowErrors.push('Name is required.');
            }
            if (!fieldValue('ef_race_' + rid)) {
                rowErrors.push('Race is required.');
            }
            var efNat = fieldValue('ef_nat_sel_' + rid);
            if (!efNat) {
                rowErrors.push('Nationality is required.');
            } else if (efNat === 'OTHERS' && !fieldValue('ef_nat_other_' + rid)) {
                rowErrors.push('Nationality (Others) must be specified.');
            }
            rowErrors.push('Customer details not confirmed. Click Update.');
            offender = offender || editPanel;
        } else if (newOpen) {
            if (!fieldValue('nc_name_' + rid)) {
                rowErrors.push('Name is required.');
            }
            var radios = document.getElementsByName('ic_type_' + rid);
            var icType = 'new';
            for (var r = 0; r < radios.length; r++) {
                if (radios[r].checked) {
                    icType = radios[r].value;
                    break;
                }
            }
            if (icType === 'new' && (!fieldValue('nc_ic1_' + rid) || !fieldValue('nc_ic2_' + rid) || !fieldValue('nc_ic3_' + rid))) {
                rowErrors.push('IC number is required.');
            }
            if (!fieldValue('nc_phone_' + rid)) {
                rowErrors.push('Phone is required.');
            }
            if (!fieldValue('nc_race_' + rid)) {
                rowErrors.push('Race is required.');
            }
            var ncNat = fieldValue('nc_nat_sel_' + rid);
            if (!ncNat) {
                rowErrors.push('Nationality is required.');
            } else if (ncNat === 'OTHERS' && !fieldValue('nc_nat_other_' + rid)) {
                rowErrors.push('Nationality (Others) must be specified.');
            }
            rowErrors.push('New customer not saved. Click Save Customer.');
            offender = offender || newPanel;
        } else if (!fieldValue('cust_ic_' + rid) || !fieldValue('customer_name_' + rid)) {
            rowErrors.push(fieldValue('ic_input_' + rid) === '' ? 'Customer is required.' : 'Customer not found. Select from the list or add a new customer.');
            offender = offender || icInputEl;
        }

        markInvalid(dateInputs[k], dateBad);
        markInvalid(clinicEl, clinicBad);
        markInvalid(icInputEl, editOpen || newOpen || (!fieldValue('cust_ic_' + rid) || !fieldValue('customer_name_' + rid)));

        if (rowErrors.length > 0) {
            errors.push(label + ': ' + rowErrors.join(' '));
            if (!firstOffender) {
                firstOffender = offender;
            }
        }
    }

    var formErrorEl = document.getElementById('form_error_msg');
    if (errors.length > 0) {
        if (formErrorEl) {
            var html = '<ul style="margin:0;padding-left:18px;">';
            for (var e = 0; e < errors.length; e++) {
                html += '<li>' + escapeHtml(errors[e]) + '</li>';
            }
            formErrorEl.innerHTML = html + '</ul>';
            formErrorEl.style.display = 'block';
        }
        if (firstOffender) {
            firstOffender.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
        return false;
    }
    if (formErrorEl) {
        formErrorEl.innerHTML = '';
        formErrorEl.style.display = 'none';
    }
    return true;
}

function saveNewCustInline(rid) {
    var radios = document.getElementsByName("ic_type_" + rid);
    var icType = 'new';
    for (var i = 0; i < radios.length; i++) {
        if (radios[i].checked) {
            icType = radios[i].value;
            break;
        }
    }
    var name = document.getElementById("nc_name_" + rid).value.trim();
    var phone = document.getElementById("nc_phone_" + rid).value.trim();
    var child = document.getElementById("nc_child_" + rid).value;
    var language = document.getElementById("nc_lang_" + rid).value;
    var race = document.getElementById("nc_race_" + rid).value;
    var email = document.getElementById("nc_email_" + rid).value.trim();
    var addr = document.getElementById("nc_addr_" + rid).value.trim();
    var natSel = document.getElementById("nc_nat_sel_" + rid);
    var natOther = document.getElementById("nc_nat_other_" + rid);
    var msgEl = document.getElementById("nc_msg_" + rid);

    if (!name || !phone || !race || !natSel.value) {
        msgEl.innerHTML = "<font color='red'>Please fill in all required fields.</font>";
        return;
    }
    var nationality;
    if (natSel.value === 'OTHERS') {
        if (natOther.value.trim() === '') {
            msgEl.innerHTML = "<font color='red'>Please specify nationality.</font>";
            return;
        }
        nationality = natOther.value.toUpperCase();
    } else {
        nationality = natSel.value;
    }

    var url, params;
    if (icType === 'new') {
        var ic1 = document.getElementById("nc_ic1_" + rid).value.trim();
        var ic2 = document.getElementById("nc_ic2_" + rid).value.trim();
        var ic3 = document.getElementById("nc_ic3_" + rid).value.trim();
        if (!ic1 || !ic2 || !ic3) {
            msgEl.innerHTML = "<font color='red'>Please fill in IC number.</font>";
            return;
        }
        url = "vaccine_customer_save.php";
        params = "name=" + encodeURIComponent(name) +
            "&ic1=" + encodeURIComponent(ic1) +
            "&ic2=" + encodeURIComponent(ic2) +
            "&ic3=" + encodeURIComponent(ic3) +
            "&phone=" + encodeURIComponent(phone) +
            "&child_num=" + encodeURIComponent(child) +
            "&language=" + encodeURIComponent(language) +
            "&race=" + encodeURIComponent(race) +
            "&nationality=" + encodeURIComponent(nationality) +
            "&email=" + encodeURIComponent(email) +
            "&addr=" + encodeURIComponent(addr);
    } else {
        var icOld = document.getElementById("nc_ic_old_" + rid).value.trim();
        var birth = document.getElementById("nc_birth_" + rid).value.trim();
        var genders = document.getElementsByName("nc_gender_" + rid);
        var gender = '';
        for (var j = 0; j < genders.length; j++) {
            if (genders[j].checked) {
                gender = genders[j].value;
                break;
            }
        }
        if (!icOld) {
            msgEl.innerHTML = "<font color='red'>Please fill in IC / Passport number.</font>";
            return;
        }
        url = "vaccine_customer_save_old.php";
        params = "name=" + encodeURIComponent(name) +
            "&ic=" + encodeURIComponent(icOld) +
            "&birth_date=" + encodeURIComponent(birth) +
            "&gender=" + encodeURIComponent(gender) +
            "&phone=" + encodeURIComponent(phone) +
            "&child_num=" + encodeURIComponent(child) +
            "&language=" + encodeURIComponent(language) +
            "&race=" + encodeURIComponent(race) +
            "&nationality=" + encodeURIComponent(nationality) +
            "&email=" + encodeURIComponent(email) +
            "&addr=" + encodeURIComponent(addr);
    }

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var res = JSON.parse(xhr.responseText);
            if (res.success) {
                document.getElementById("ic_input_" + rid).value = res.ic;
                document.getElementById("cust_ic_" + rid).value = res.ic;
                document.getElementById("customer_name_" + rid).value = res.name;
                document.getElementById("phone2_" + rid).value = res.phone;
                document.getElementById("child_num_" + rid).value = res.child_num;
                closeNewCustInline(rid);
                var xhr2 = new XMLHttpRequest();
                xhr2.onreadystatechange = function() {
                    if (xhr2.readyState == 4 && xhr2.status == 200) {
                        var d2 = JSON.parse(xhr2.responseText);
                        if (d2.success) {
                            populateEditPanel(rid, d2);
                            document.getElementById("cust_panel_" + rid).style.display = 'block';
                            document.getElementById("new_cust_btn_" + rid).style.display = 'none';
                            document.getElementById("new_cust_panel_" + rid).style.display = 'none';
                            var confEl = document.getElementById("ef_confirmed_" + rid);
                            confEl.style.display = 'block';
                            confEl.innerHTML = 'Registered: ' + d2.name;
                        }
                    }
                };
                xhr2.open("GET", "vaccine_customer_fetch.php?ic=" + encodeURIComponent(res.ic), true);
                xhr2.send();
                document.getElementById("cust_notfound_" + rid).style.display = 'none';
                msgEl.innerHTML = '';
            } else {
                msgEl.innerHTML = "<font color='red'>" + res.error + "</font>";
            }
        }
    };
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send(params);
}
</script>
<?php

} else {
?>
<script type="text/javascript" src="../common/js/jquery-1.5.1.js"></script>
<script type='text/javascript' src="../common/js/jquery.autocomplete.js"></script>
<link rel="stylesheet" type="text/css" href="../common/css/jquery.autocomplete.css" />
<div class="header" style="position:relative;">
    <b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
    <h1 class="headerH1"><img src='../common/img/cash.png' width='18px'> Import Xilnex Invoice</h1>
    <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<div class="idx-panel">
    <?php
    $get_campaign_date = isset($_GET['campaign_date']) ? trim($_GET['campaign_date']) : '';
    $get_campaign_id   = isset($_GET['campaign_id'])   ? (int)$_GET['campaign_id']   : 0;
    ?>
    <form id="FormName" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName">
        <input type="hidden" name="campaign_id" value="<?php echo $get_campaign_id; ?>" />
        <table class="myTable">
            <tr>
                <th>Invoice No. <font color='red'>*</font> :</th>
                <td>
                    <textarea name='id' rows='10' required style="width:100%;"
                        placeholder='1000001&#10;1000002&#10;1000003'></textarea>
                </td>
            </tr>
            <tr>
                <th>Campaign Date :</th>
                <td>
                    <input type="date" name="campaign_date" style="width:100%;"
                        value="<?php echo htmlspecialchars($get_campaign_date); ?>" />
                    <small style="display:block;margin-top:4px;">Optional. Pre-fills campaign date for all rows.</small>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-top:14px !important;">
                    <button type="submit" name="submit" id="submit" class="upd-submit">Retrieve</button>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-top:8px !important;color:#6b7280;">
                    <small>One invoice number per line. Make sure Xilnex is fully synced before importing.</small>
                </td>
            </tr>
        </table>
    </form>
</div>
<?php
}
$connect = 0;
include('../common/index_adv.php');
?>