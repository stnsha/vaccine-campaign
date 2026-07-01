<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
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
.myTable tbody tr {
    border-bottom: 1px solid #e5e7eb !important;
}
.myTable tbody tr:hover {
    background: #f8fafc !important;
}
.myTable td {
    color: #111 !important;
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
    cursor: pointer !important;
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
    cursor: pointer !important;
}
.tbl-del-link:hover { background: #fff0f0 !important; }
.filter-bar {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
    margin-bottom: 12px !important;
}
.filter-bar input[type="text"],
.filter-bar select {
    border-radius: 8px !important;
    padding: 5px 8px !important;
    border: 1px solid #cfcfcf !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    height: 32px !important;
    box-sizing: border-box !important;
    background: #fff !important;
}
.filter-bar input[type="text"]:focus,
.filter-bar select:focus {
    border-color: rgba(0,91,150,.55) !important;
    box-shadow: 0 0 0 3px rgba(0,91,150,.12) !important;
    outline: none !important;
}
.idx-pagination {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
}
.idx-pagination img {
    height: 17px !important;
    width: auto !important;
}
.idx-pagination span {
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
}
.idx-pagination a {
    display: inline-flex !important;
    align-items: center !important;
    opacity: 0.7 !important;
    text-decoration: none !important;
}
.idx-pagination a:hover {
    opacity: 1 !important;
    text-decoration: none !important;
}
.idx-pagination select {
    border-radius: 6px !important;
    padding: 3px 6px !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    box-sizing: border-box !important;
}
</style>
<?php
$vaccine_arr = array();
$query6  = "select * from `vaccine_type` where recycle=0";
$result6 = mysqli_query($conn, $query6);
$num6    = mysqli_num_rows($result6);
if ($num6 > 0) {
    $i6 = 0;
    while ($row6 = $result6->fetch_assoc()) {
        $id           = stripslashes($row6['id']);
        $vaccine_name = stripslashes($row6['vaccine_name']);
        $vaccine_arr[$id] = $vaccine_name;
        $i6++;
    }
}

if (isset($_GET['d'])) {
    $del_id  = trim(mysqli_real_escape_string($conn, $_GET['id']));
    $query2  = "delete from `vaccine_code` where `id`='$del_id'";
    $result2 = mysqli_query($conn, $query2);
    echo "<div style='background:#d1fae5;border-left:3px solid #059669;border-radius:6px;padding:8px 14px;margin:6px 0;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;color:#065f46 !important;'>Vaccine code deleted.</div>";
}

$option = '';
if (isset($_REQUEST['s'])) {
    $option1 = '';
    $option2 = '';
    $key                  = trim(mysqli_real_escape_string($conn, $_REQUEST['key']));
    $selected_vaccine_type = trim(mysqli_real_escape_string($conn, $_REQUEST['vaccine_type']));
    if ($key) { $option1 = "and (`vaccine_code`.`item_code` like '$key%' or `name` like '$key%')"; }
    if ($selected_vaccine_type) { $option2 = "and `vaccine_type`='$selected_vaccine_type'"; }
    $option = "$option1 $option2";
}

if (isset($_REQUEST['pageno'])) { $pageno = $_REQUEST['pageno']; } else { $pageno = 1; }

$query    = "select count(*) from vaccine_code left join simple on vaccine_code.item_code=simple.item_code where 1=1 $option order by `vaccine_type`, name";
$result   = mysqli_query($conn, $query) or die(mysqli_error($conn));
$num_rows = mysqli_fetch_row($result);
$numrows  = $num_rows[0];

$lastpage = ceil($numrows / $rows_per_page);
$pageno   = (int)$pageno;
if ($pageno > $lastpage) { $pageno = $lastpage; }
if ($pageno < 1)         { $pageno = 1; }

$limit  = 'LIMIT ' . ($pageno - 1) * $rows_per_page . ',' . $rows_per_page;
$query  = "select `vaccine_code`.`id` as `id`, `vaccine_code`.`item_code`, `vaccine_type`, `name` from vaccine_code left join simple on vaccine_code.item_code=simple.item_code where 1=1 $option order by `vaccine_type`, name $limit";
$result = mysqli_query($conn, $query);
$num    = mysqli_num_rows($result);
?>
<div class="idx-panel">
    <!-- Toolbar -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
        <div style="display:flex;align-items:center;gap:8px;">
            <?php if ($vaccine_autho == '1') { ?>
            <a href="vaccine_add_code.php" class="upd-submit">+ Add Vaccine</a>
            <?php } ?>
            <a href="vaccine_index_item.php" class="upd-back">Show All</a>
        </div>
        <!-- Filter -->
        <form id="FormName" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName">
            <input type="hidden" name="s" value="1" />
            <div class="filter-bar" style="margin-bottom:0 !important;">
                <?php
                echo "<select name='vaccine_type' onchange='submit()' style='min-width:120px;'>";
                echo "<option value=''>All Types</option>";
                foreach ($vaccine_arr as $vid => $vname) {
                    $s = ($selected_vaccine_type == $vid) ? 'selected' : '';
                    echo "<option value='$vid' $s>$vname</option>";
                }
                echo "</select>";
                ?>
                <input type="text" name="key" autocomplete="off" placeholder="Item Code / Description"
                    value="<?php echo isset($key) ? htmlspecialchars($key) : ''; ?>" autofocus style="min-width:200px;" />
            </div>
        </form>
    </div>

    <!-- Pagination -->
    <div style="margin-bottom:10px;">
        <div class="idx-pagination">
            <?php
            if ($pageno == 1) {
                echo "<img src='../common/img/fast_back_grey.png'> <img src='../common/img/backward_grey.png'>";
            } else {
                $prevpage = $pageno - 1;
                echo "<a href='{$_SERVER['PHP_SELF']}?pageno=1&key=$key&vaccine_type=$selected_vaccine_type&s=1' title='First Page'><img src='../common/img/fast_back.png'></a>";
                echo "<a href='{$_SERVER['PHP_SELF']}?pageno=$prevpage&key=$key&vaccine_type=$selected_vaccine_type&s=1' title='Previous Page'><img src='../common/img/backward.png'></a>";
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
                <input type="hidden" name="key" value="<?php echo isset($key) ? htmlspecialchars($key) : ''; ?>" />
                <input type="hidden" name="s" value="1" />
                <input type="hidden" name="vaccine_type" value="<?php echo isset($selected_vaccine_type) ? htmlspecialchars($selected_vaccine_type) : ''; ?>" />
            </form>
            <span>of <?php echo $lastpage; ?> )</span>
            <?php
            if ($pageno >= $lastpage) {
                echo "<img src='../common/img/front_grey.png'> <img src='../common/img/fast_front_grey.png'>";
            } else {
                $nextpage = $pageno + 1;
                echo "<a href='{$_SERVER['PHP_SELF']}?pageno=$nextpage&key=$key&vaccine_type=$selected_vaccine_type&s=1' title='Following Page'><img src='../common/img/front.png'></a>";
                echo "<a href='{$_SERVER['PHP_SELF']}?pageno=$lastpage&key=$key&vaccine_type=$selected_vaccine_type&s=1' title='Last Page'><img src='../common/img/fast_front.png'></a>";
            }
            ?>
            <span style="color:#6b7280 !important;margin-left:4px;"><?php echo $numrows; ?> record(s)</span>
        </div>
    </div>

    <!-- Data table -->
    <table class="myTable">
        <thead>
            <tr>
                <th style="width:5%;text-align:center !important;">No.</th>
                <th style="width:12%;">Type</th>
                <th style="width:12%;">Item Code</th>
                <th>Description</th>
                <?php if ($vaccine_autho == '1') { echo "<th style='width:10%;text-align:center !important;'>Actions</th>"; } ?>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($num > 0) {
            $i = 0;
            while ($row = $result->fetch_assoc()) {
                $vaccine_id   = stripslashes($row['id']);
                $vaccine_type = stripslashes($row['vaccine_type']);
                $item_code    = stripslashes($row['item_code']);
                $description  = stripslashes($row['name']);
                $r = (($pageno - 1) * $rows_per_page) + $i + 1;
                ?>
                <tr>
                    <td style="text-align:center !important;"><?php echo $r; ?></td>
                    <td><?php echo htmlspecialchars($vaccine_arr[$vaccine_type]); ?></td>
                    <td><?php echo htmlspecialchars($item_code); ?></td>
                    <td><?php echo htmlspecialchars($description); ?></td>
                    <?php if ($vaccine_autho == '1') { ?>
                    <td style="text-align:center !important;white-space:nowrap !important;">
                        <a href="vaccine_update_code.php?id=<?php echo $vaccine_id; ?>" class="tbl-action-link" title="Edit">
                            <img src="../common/img/edit.png" width="14px" /> Edit
                        </a>
                        <a href="vaccine_index_item.php?id=<?php echo $vaccine_id; ?>&d=1" class="tbl-del-link"
                            title="Delete" onclick="return confirm('Are you sure to remove this vaccine code?')">
                            <img src="../common/img/trash.png" width="14px" /> Delete
                        </a>
                    </td>
                    <?php } ?>
                </tr>
                <?php
                $i++;
            }
        } else { ?>
            <tr><td colspan="5" style="text-align:center !important;color:#6b7280 !important;padding:20px !important;">No records found.</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php
$connect = 0;
include('../common/index_adv.php');
?>
