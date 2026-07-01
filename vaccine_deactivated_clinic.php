<?php
require_once('../lock_adv.php');
$connect = 1;
include('../common/index_adv.php');
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
.idx-filters input[type="text"] {
    border-radius: 8px !important;
    padding: 5px 8px !important;
    border: 1px solid #cfcfcf !important;
    background: #fff !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    box-sizing: border-box !important;
    height: 32px !important;
    min-width: 220px !important;
}
.idx-filters input[type="text"]:focus {
    outline: none !important;
    border-color: rgba(0,91,150,.55) !important;
    box-shadow: 0 0 0 3px rgba(0,91,150,.12) !important;
}
.idx-icon-link {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 4px !important;
    padding: 5px 6px !important;
    text-decoration: none !important;
    opacity: 0.7 !important;
    position: relative !important;
    font-size: 12px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    color: #111 !important;
}
.idx-icon-link:hover { opacity: 1 !important; text-decoration: none !important; }
.idx-icon-link img { height: 17px !important; width: auto !important; }
.idx-icon-link::after {
    content: attr(data-tooltip);
    position: absolute !important;
    bottom: calc(100% + 6px) !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    background: #1f2937 !important;
    color: #fff !important;
    font-size: 11px !important;
    white-space: nowrap !important;
    padding: 4px 8px !important;
    border-radius: 5px !important;
    pointer-events: none !important;
    opacity: 0 !important;
    transition: opacity .15s ease !important;
    z-index: 10 !important;
}
.idx-icon-link:hover::after { opacity: 1 !important; }
.idx-icon-current {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    padding: 5px 6px !important;
    opacity: 1 !important;
    font-size: 12px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    color: #111 !important;
    font-weight: bold !important;
}
.idx-icon-current img { height: 17px !important; width: auto !important; }
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
.myTable tbody td { vertical-align: top !important; color: #111 !important; }
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
.tbl-reactivate-link {
    display: inline-flex !important;
    align-items: center !important;
    gap: 3px !important;
    font-size: 12px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    color: #059669 !important;
    text-decoration: none !important;
    padding: 2px 6px !important;
    border-radius: 5px !important;
}
.tbl-reactivate-link:hover { background: #ecfdf5 !important; }
</style>
<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

$can_edit = (strpos(strtolower($status_semasa), 'pharmacist') !== false
    || $status_semasa == 'Assistant Branch Manager'
    || $status_semasa == 'Branch Manager'
    || $status_semasa == 'Associate Area Manager'
    || $status_semasa == 'Area Manager'
    || $vaccine_autho == 1);

if (isset($_GET['d'])) {
    $del_id = trim(mysqli_real_escape_string($conn, $_GET['id']));
    mysqli_query($conn, "update `gp_clinics` set `is_active`=0 where `id`='$del_id'");
}

$option = '';
$key    = '';
if (isset($_REQUEST['s'])) {
    $key       = trim(mysqli_real_escape_string($conn, $_REQUEST['key']));
    $clinic_id = trim(mysqli_real_escape_string($conn, $_GET['id']));
    if ($clinic_id) {
        $option = "and `id`='$clinic_id'";
    } elseif ($key) {
        $option = "and (`name` like '%$key%' or `address` like '%$key%' or `dr_name` like '%$key%')";
    }
}

if (isset($_REQUEST['pageno'])) { $pageno = $_REQUEST['pageno']; } else { $pageno = 1; }

$query    = "SELECT count(*) FROM `gp_clinics` WHERE `is_active`='0' $option";
$result   = mysqli_query($conn, $query) or die(mysqli_error($conn));
$num_rows = mysqli_fetch_row($result);
$numrows  = $num_rows[0];

$lastpage = ceil($numrows / $rows_per_page);
$pageno   = (int)$pageno;
if ($pageno > $lastpage) { $pageno = $lastpage; }
if ($pageno < 1)         { $pageno = 1; }

$limit  = 'LIMIT ' . ($pageno - 1) * $rows_per_page . ',' . $rows_per_page;
$query  = "SELECT * FROM `gp_clinics` WHERE `is_active`='0' $option order by `name` $limit";
$result = mysqli_query($conn, $query);
$num    = mysqli_num_rows($result);
?>
<div class="idx-panel">
    <div class="idx-toolbar">
        <!-- Left: icon actions + pagination -->
        <div class="idx-actions">
            <?php if ($can_edit) { ?>
            <a href="vaccine_add_clinic.php" class="idx-icon-link" data-tooltip="Add Clinic"><img src="../common/img/plus.png" /></a>
            <span class="idx-sep"></span>
            <?php } ?>
            <a href="vaccine_index_clinic.php" class="idx-icon-link" data-tooltip="Active Clinics"><img src="../common/img/star.png" style="filter:grayscale(100%) !important;" /> Active</a>
            <span class="idx-sep"></span>
            <span class="idx-icon-current" title="Deactivated Clinics"><img src="../common/img/star.png" /> Deactivated</span>
            <span class="idx-sep"></span>
            <div class="idx-pagination">
                <?php
                if ($pageno == 1) {
                    echo "<img src='../common/img/fast_back_grey.png'> <img src='../common/img/backward_grey.png'>";
                } else {
                    $prevpage = $pageno - 1;
                    echo "<a href='{$_SERVER['PHP_SELF']}?pageno=1&key=$key&s=1' title='First Page'><img src='../common/img/fast_back.png'></a>";
                    echo "<a href='{$_SERVER['PHP_SELF']}?pageno=$prevpage&key=$key&s=1' title='Previous Page'><img src='../common/img/backward.png'></a>";
                }
                ?>
                <span>( Page</span>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                    <select name="pageno" onchange="submit()" style="border-radius:6px;padding:3px 6px;font-size:13px;">
                        <?php
                        $displayPages = max(1, $lastpage);
                        for ($i = 1; $i <= $displayPages; $i++) {
                            $sel = ($pageno == $i) ? 'selected' : '';
                            echo "<option value='$i' $sel>$i</option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="key" value="<?php echo htmlspecialchars($key); ?>" />
                    <input type="hidden" name="s" value="1" />
                </form>
                <span>of <?php echo $lastpage; ?> )</span>
                <?php
                if ($pageno >= $lastpage) {
                    echo "<img src='../common/img/front_grey.png'> <img src='../common/img/fast_front_grey.png'>";
                } else {
                    $nextpage = $pageno + 1;
                    echo "<a href='{$_SERVER['PHP_SELF']}?pageno=$nextpage&key=$key&s=1' title='Following Page'><img src='../common/img/front.png'></a>";
                    echo "<a href='{$_SERVER['PHP_SELF']}?pageno=$lastpage&key=$key&s=1' title='Last Page'><img src='../common/img/fast_front.png'></a>";
                }
                ?>
                <span style="color:#6b7280 !important;margin-left:4px;"><?php echo $numrows; ?> record(s)</span>
            </div>
        </div>

        <!-- Right: search + reset -->
        <form id="FormName" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName">
            <input type="hidden" name="s" value="1" />
            <div class="idx-filters">
                <input type="text" name="key" autocomplete="off" placeholder="Clinic Name / Doctor / Address"
                    value="<?php echo htmlspecialchars($key); ?>" autofocus />
                <a href="vaccine_deactivated_clinic.php" class="upd-submit" title="Reset search">Reset</a>
            </div>
        </form>
    </div>

    <!-- Data table -->
    <table class="myTable">
        <thead>
            <tr>
                <th style="width:4%;text-align:center !important;">No.</th>
                <th style="width:22%;">Clinic Name</th>
                <th style="width:13%;">Contact</th>
                <th style="width:17%;">Doctor In Charge</th>
                <th>Address</th>
                <?php if ($can_edit) { echo "<th style='width:10%;text-align:center !important;'>Actions</th>"; } ?>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($num > 0) {
            $i = 0;
            while ($row = $result->fetch_assoc()) {
                $clinic_id = stripslashes($row['id']);
                $clinic    = stripslashes($row['name']);
                $c_phone   = stripslashes($row['phone_1']);
                $dr_name   = stripslashes($row['dr_name']);
                $address   = stripslashes($row['address']);

                $hp      = preg_replace('/\D/', '', $c_phone);
                $prefix2 = '6';
                if (substr("$hp", 0, 2) == '01' || substr("$hp", 0, 1) == '1') {
                    $wa_link = "<a href='javascript:void(0);' onclick=\"window.open(&quot;https://api.whatsapp.com/send?phone=$prefix2$hp&text=Dear%20$dr_name,%0A%0A&language=en&quot;,&quot;Ratting&quot;,&quot;width=1,height=1,left=1,top=1,toolbar=no,scrollbars=no,menubar=no,resizable=no&quot;);\" title='Send Whatsapp'><img src='../common/img/wa.png' width='15px'> " . htmlspecialchars($hp) . "</a>";
                } else {
                    $wa_link = htmlspecialchars($hp);
                }

                $r = (($pageno - 1) * $rows_per_page) + $i + 1;
                ?>
                <tr>
                    <td style="text-align:center !important;"><?php echo $r; ?></td>
                    <td><?php echo htmlspecialchars($clinic); ?></td>
                    <td><?php echo $wa_link; ?></td>
                    <td><?php echo htmlspecialchars($dr_name); ?></td>
                    <td><?php echo htmlspecialchars($address); ?></td>
                    <?php if ($can_edit) { ?>
                    <td style="text-align:center !important;white-space:nowrap !important;">
                        <a href="vaccine_update_clinic.php?id=<?php echo $clinic_id; ?>" class="tbl-action-link" title="Edit">
                            <img src="../common/img/edit.png" width="14px" /> Edit
                        </a>
                        <a href="vaccine_index_clinic.php?id=<?php echo $clinic_id; ?>&r=1" class="tbl-reactivate-link"
                            title="Reactivate" onclick="return confirm('Reactivate this clinic?')">
                            <img src="../common/img/loyalty.png" width="14px" /> Reactivate
                        </a>
                    </td>
                    <?php } ?>
                </tr>
                <?php
                $i++;
            }
        } else { ?>
            <tr><td colspan="6" style="text-align:center !important;color:#6b7280 !important;padding:20px !important;">No deactivated clinics found.</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php
$connect = 0;
include('../common/index_adv.php');
?>
