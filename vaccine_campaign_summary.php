<?php
require_once('../lock_adv.php');
$connect = 1;
include('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');

if($vaccine_autho != '1') {
    echo "<fieldset class='center'><img src='../common/img/warning.png'><br/>You are not authorized to access this page.<br/><a href='vaccine_calendar.php'>Back to Calendar</a></fieldset>";
    $connect = 0;
    include('../common/index_adv.php');
    exit;
}

// Calculate default date range: today to upcoming Sunday
$day_of_week = (int)date('N'); // 1=Mon, 7=Sun
if($day_of_week == 7) {
    $days_to_sun = 0; // Today is Sunday
} else {
    $days_to_sun = 7 - $day_of_week;
}
$default_from = date('Y-m-d');
$default_to   = date('Y-m-d', strtotime('+' . $days_to_sun . ' days'));

$date_from = isset($_GET['date_from']) && $_GET['date_from'] != '' ? trim(mysqli_real_escape_string($conn, $_GET['date_from'])) : $default_from;
$date_to   = isset($_GET['date_to'])   && $_GET['date_to']   != '' ? trim(mysqli_real_escape_string($conn, $_GET['date_to']))   : $default_to;

// Clamp date_to to at least date_from
if($date_to < $date_from) {
    $date_to = $date_from;
}

$filter_type   = isset($_GET['filter_type'])   ? trim(mysqli_real_escape_string($conn, $_GET['filter_type']))   : '';
$filter_outlet = isset($_GET['filter_outlet']) ? trim(mysqli_real_escape_string($conn, $_GET['filter_outlet'])) : '';

$where = array("vc.v_date BETWEEN '$date_from' AND '$date_to'", "vc.status != '2'");

if($filter_type != '') {
    $where[] = "vc.type='" . $filter_type . "'";
}
if($filter_outlet != '') {
    $where[] = "vc.outlets='" . $filter_outlet . "'";
}

$where_sql = implode(' AND ', $where);

$per_page    = 20;
$count_query = "SELECT COUNT(*) AS cnt
                FROM vaccine_campaign vc
                LEFT JOIN outlet o ON vc.outlets = o.id
                WHERE $where_sql";
$count_result = mysqli_query($conn, $count_query);
$count_row    = mysqli_fetch_assoc($count_result);
$total        = (int)$count_row['cnt'];
$total_pages  = ($total > 0) ? (int)ceil($total / $per_page) : 1;
$page         = isset($_GET['page']) ? max(1, min((int)$_GET['page'], $total_pages)) : 1;
$offset       = ($page - 1) * $per_page;

$query = "SELECT vc.id, vc.v_date, vc.type, vc.status,
                 o.code AS outlet_code, o.comp_name AS outlet_name,
                 cl.name AS clinic_name, cl.dr_name,
                 (SELECT COUNT(*) FROM vaccine_trans
                   WHERE outlet_id = vc.outlets AND DATE(v_date) = vc.v_date AND recycle = 0) AS total_booked,
                 (SELECT COUNT(*) FROM vaccine_trans
                   WHERE outlet_id = vc.outlets AND DATE(v_date) = vc.v_date AND recycle = 0 AND status = '1') AS total_vaccinated
          FROM vaccine_campaign vc
          LEFT JOIN outlet o ON vc.outlets = o.id
          LEFT JOIN gp_clinics cl ON vc.clinic = cl.id
          WHERE $where_sql
          ORDER BY vc.v_date ASC, o.code ASC
          LIMIT $per_page OFFSET $offset";

$result = mysqli_query($conn, $query);

// Outlets for filter dropdown
$outlet_result = mysqli_query($conn, "SELECT id, code, comp_name FROM outlet WHERE recycle=0 ORDER BY code ASC");

// Query string carrying the active filters, reused by pagination links
$filter_qs = 'date_from=' . urlencode($date_from)
    . '&date_to='       . urlencode($date_to)
    . '&filter_type='   . urlencode($filter_type)
    . '&filter_outlet=' . urlencode($filter_outlet);
?>

<style type="text/css">
.idx-panel {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,.08);
    padding: 14px 18px;
    margin: 6px 0 10px;
}
.idx-toolbar {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    margin-bottom: 16px !important;
}
.idx-actions {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    flex-wrap: wrap !important;
}
.idx-filters {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    flex-wrap: wrap !important;
}
.idx-filters input[type="date"],
.idx-filters select {
    border-radius: 8px !important;
    padding: 5px 8px !important;
    border: 1px solid #cfcfcf !important;
    background: #fff !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    box-sizing: border-box !important;
    height: 32px !important;
}
.idx-filters input[type="date"]:focus,
.idx-filters select:focus {
    outline: none !important;
    border-color: rgba(0,91,150,.55) !important;
    box-shadow: 0 0 0 3px rgba(0,91,150,.12) !important;
}
.idx-sep {
    display: inline-block !important;
    border-left: 2px solid #d0d7de !important;
    height: 16px !important;
    vertical-align: middle !important;
}
.idx-pagination {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
}
.idx-pagination img { height: 17px !important; width: auto !important; }
.idx-pagination span { font-size: 13px !important; font-family: Arial, Helvetica, sans-serif !important; }
.idx-pagination a { display: inline-flex !important; align-items: center !important; opacity: 0.7 !important; text-decoration: none !important; }
.idx-pagination a:hover { opacity: 1 !important; }
.idx-pagination select { border-radius: 6px !important; padding: 3px 6px !important; font-size: 13px !important; }
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
}
a.upd-success:hover, .upd-success:hover { background: #ecfdf5 !important; border-color: #059669 !important; }
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
.camp-badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 2px 10px !important;
    border-radius: 20px !important;
    font-size: 11px !important;
    font-weight: bold !important;
    color: #fff !important;
}
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
</style>

<div class="header" style="position: relative;">
    <b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
    <h1 class="headerH1"><img src='../common/img/vaccine.png' width='22px'> Vaccine Campaign Summary</h1>
    <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>

<div class="idx-panel">
    <form method="get" action="vaccine_campaign_summary.php">
        <div class="idx-toolbar">
            <div class="idx-filters">
                <span style="font-size:13px;font-weight:bold;color:#333;">From:</span>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                <span style="font-size:13px;font-weight:bold;color:#333;">To:</span>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                <select name="filter_type">
                    <option value="">All Types</option>
                    <option value="1" <?php echo $filter_type == '1' ? 'selected' : ''; ?>>HQ Initiated</option>
                    <option value="2" <?php echo $filter_type == '2' ? 'selected' : ''; ?>>Outlet Initiated</option>
                </select>
                <select name="filter_outlet">
                    <option value="">All Outlets</option>
                    <?php
                    if($outlet_result && mysqli_num_rows($outlet_result) > 0) {
                        while($o = mysqli_fetch_assoc($outlet_result)) {
                            $sel = ($filter_outlet == $o['id']) ? 'selected' : '';
                            echo "<option value='" . $o['id'] . "' $sel>" . htmlspecialchars($o['code'] . ' - ' . $o['comp_name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="idx-actions">
                <button type="submit" class="upd-submit">Search</button>
                <a href="vaccine_campaign_summary.php" class="upd-back">Reset</a>
                <?php if($total > 0) { ?>
                <a href="vaccine_campaign_export.php?<?php echo $filter_qs; ?>" class="upd-success">Export Excel</a>
                <?php } ?>
            </div>
        </div>
    </form>
</div>

<div class="idx-panel">
    <div style="margin-bottom:8px;font-size:13px;font-family:Arial, Helvetica, sans-serif;">
        <?php
        $showing_from = $total > 0 ? $offset + 1 : 0;
        $showing_to   = min($offset + $per_page, $total);
        echo "<strong>$showing_from&ndash;$showing_to</strong> of <strong>$total</strong> campaign(s) &mdash; ";
        echo date('d M Y', strtotime($date_from)) . ' to ' . date('d M Y', strtotime($date_to));
        ?>
    </div>

    <?php if($total == 0) { ?>
    <p style="color:#6b7280;"><i>No campaigns found for the selected date range.</i></p>
    <?php } else { ?>
    <table class="myTable">
        <thead>
        <tr>
            <th style="width:3%;text-align:center;">No.</th>
            <th style="width:10%;">Date</th>
            <th style="width:7%;">Day</th>
            <th style="width:8%;">Outlet Code</th>
            <th>Outlet Name</th>
            <th style="width:10%;">Type</th>
            <th>Clinic</th>
            <th style="width:13%;">Doctor</th>
            <th style="width:12%;">Status</th>
            <th style="width:7%;">Booked</th>
            <th style="width:8%;">Vaccinated</th>
            <th style="width:5%;">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $no = $offset + 1;
        while($row = mysqli_fetch_assoc($result)) {
            $camp_date = $row['v_date'];
            $day_name  = date('l', strtotime($camp_date));
            $camp_type = $row['type'];

            if($camp_type == '1') {
                $type_badge = '<span class="camp-badge" style="background:#3366FF !important;">HQ</span> HQ Initiated';
            } else {
                $type_badge = '<span class="camp-badge" style="background:#FF6600 !important;">Outlet</span> Outlet Initiated';
            }

            $clinic_disp = !empty($row['clinic_name'])
                ? htmlspecialchars($row['clinic_name'])
                : '<span style="color:#9ca3af;font-style:italic;">-</span>';
            $doctor_disp = !empty($row['dr_name'])
                ? htmlspecialchars($row['dr_name'])
                : '<span style="color:#9ca3af;font-style:italic;">-</span>';

            if($row['status'] == '0') {
                $status_disp = '<span style="color:#FF6600;">Pending Acknowledgement</span>';
            } else if($row['status'] == '1') {
                $days_left = (strtotime($camp_date) - strtotime(date('Y-m-d'))) / 86400;
                if($days_left > 0) {
                    $status_disp = '<span style="color:#059669;">Recruiting</span>';
                } else if(round($days_left) == 0) {
                    $status_disp = '<span style="color:#005B96;">Today is the Vaccine Event</span>';
                } else {
                    $status_disp = '<span style="color:#6b7280;">Completed</span>';
                }
            } else {
                $status_disp = '<span style="color:#CC0000;">Cancelled</span>';
            }

            $row_bgcolor = ($day_name == 'Saturday' || $day_name == 'Sunday') ? 'background:#fffde7 !important;' : '';
            echo "<tr style='$row_bgcolor'>";
            echo "<td style='text-align:center;'>" . $no++ . "</td>";
            echo "<td>" . date('d M Y', strtotime($camp_date)) . "</td>";
            echo "<td>" . $day_name . "</td>";
            echo "<td>" . htmlspecialchars($row['outlet_code']) . "</td>";
            echo "<td>" . htmlspecialchars($row['outlet_name']) . "</td>";
            echo "<td>" . $type_badge . "</td>";
            echo "<td>" . $clinic_disp . "</td>";
            echo "<td>" . $doctor_disp . "</td>";
            echo "<td>" . $status_disp . "</td>";
            echo "<td style='text-align:center;'>" . $row['total_booked'] . "</td>";
            echo "<td style='text-align:center;'>" . $row['total_vaccinated'] . "</td>";
            echo "<td><a href='vaccine_campaign.php?id=" . $row['id'] . "' class='tbl-action-link'>View</a></td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>

    <?php if($total_pages > 1) { ?>
    <div style="overflow:hidden; margin-top:12px;">
        <div class="idx-pagination">
            <?php
            if($page <= 1) {
                echo "<img src='../common/img/fast_back_grey.png'> <img src='../common/img/backward_grey.png'>";
            } else {
                echo "<a href='vaccine_campaign_summary.php?" . $filter_qs . "&page=1' title='First Page'><img src='../common/img/fast_back.png'></a>";
                echo "<a href='vaccine_campaign_summary.php?" . $filter_qs . "&page=" . ($page - 1) . "' title='Previous Page'><img src='../common/img/backward.png'></a>";
            }
            ?>
            <span>( Page</span>
            <form method="get" action="vaccine_campaign_summary.php" style="display:inline;">
                <select name="page" onchange="submit()">
                    <?php
                    for($p = 1; $p <= $total_pages; $p++) {
                        $sel = ($p == $page) ? 'selected' : '';
                        echo "<option value='$p' $sel>$p</option>";
                    }
                    ?>
                </select>
                <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                <input type="hidden" name="filter_type" value="<?php echo htmlspecialchars($filter_type); ?>">
                <input type="hidden" name="filter_outlet" value="<?php echo htmlspecialchars($filter_outlet); ?>">
            </form>
            <span>of <?php echo $total_pages; ?> )</span>
            <?php
            if($page >= $total_pages) {
                echo "<img src='../common/img/front_grey.png'> <img src='../common/img/fast_front_grey.png'>";
            } else {
                echo "<a href='vaccine_campaign_summary.php?" . $filter_qs . "&page=" . ($page + 1) . "' title='Next Page'><img src='../common/img/front.png'></a>";
                echo "<a href='vaccine_campaign_summary.php?" . $filter_qs . "&page=" . $total_pages . "' title='Last Page'><img src='../common/img/fast_front.png'></a>";
            }
            ?>
            <span style="color:#6b7280;margin-left:4px;"><?php echo $total; ?> campaign(s)</span>
        </div>
    </div>
    <?php } ?>

    <?php } ?>
</div>

<?php
$connect = 0;
include('../common/index_adv.php');
?>
