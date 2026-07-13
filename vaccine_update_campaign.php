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
.myTable th {
    width: 160px !important;
    font-weight: bold !important;
    background: transparent !important;
    color: inherit !important;
    white-space: nowrap !important;
    border-bottom: 1px solid #e5e7eb !important;
}
.myTable td { border-bottom: 1px solid #e5e7eb !important; }
.myTable input[type="text"],
.myTable select {
    border-radius: 8px !important;
    padding: 5px 8px !important;
    border: 1px solid #cfcfcf !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    box-sizing: border-box !important;
    background: #fff !important;
    height: 32px !important;
}
.myTable input[type="text"]:focus,
.myTable select:focus {
    border-color: rgba(0,91,150,.55) !important;
    box-shadow: 0 0 0 3px rgba(0,91,150,.12) !important;
    outline: none !important;
}
.myTable select { min-width: 200px !important; }
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
</style>
<?php
if (isset($_POST['submit'])) {
    $campaign_id = trim(mysqli_real_escape_string($conn, $_POST['id'] ?? ''));
    $v_date      = trim(mysqli_real_escape_string($conn, $_POST['v_date'] ?? ''));
    $outlets     = trim(mysqli_real_escape_string($conn, $_POST['outlets'] ?? ''));
    $clinic      = trim(mysqli_real_escape_string($conn, $_POST['clinic'] ?? ''));

    $sql2    = "SELECT `id` FROM `vaccine_campaign` WHERE `v_date`='$v_date' AND `outlets`='$outlets' AND `id`!='$campaign_id'";
    $result2 = mysqli_query($conn, $sql2);
    $row2    = $result2 ? $result2->fetch_assoc() : null;
    $dup_id  = $row2 ? $row2['id'] : null;

    if (!$dup_id) {
        $query  = "UPDATE `vaccine_campaign` SET `v_date`='$v_date', `outlets`='$outlets', `clinic`='$clinic' WHERE `id`='$campaign_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            header("location: vaccine_campaign.php?id=$campaign_id&updated=1");
            $connect = 0; include('../common/index_adv.php'); exit;
        }
    }
    ?>
    <div class="idx-panel">
        <p style="color:#c53030 !important;font-size:13px !important;">
            <b>Duplicate campaign found.</b>
            A campaign already exists for that outlet on <?php echo htmlspecialchars($v_date); ?>.
        </p>
        <div style="display:flex;align-items:center;gap:8px;margin-top:10px;">
            <a href="javascript:history.back();" class="upd-submit">Go Back</a>
            <a href="vaccine_calendar.php" class="upd-back">Back to Calendar</a>
        </div>
    </div>
    <?php
    $connect = 0; include('../common/index_adv.php'); exit;
}

$campaign_id = trim(mysqli_real_escape_string($conn, $_GET['id'] ?? ''));
$query       = "SELECT * FROM `vaccine_campaign` WHERE `id`='$campaign_id'";
$result      = mysqli_query($conn, $query);
$row         = $result ? $result->fetch_assoc() : null;

if (!$row) { ?>
    <div class="idx-panel">
        <p style="color:#c53030 !important;font-size:13px !important;">Campaign not found.</p>
        <a href="vaccine_calendar.php" class="upd-back">Back to Calendar</a>
    </div>
<?php
    $connect = 0; include('../common/index_adv.php'); exit;
}

$v_date    = stripslashes($row['v_date'] ?? '');
$outlets   = stripslashes($row['outlets'] ?? '');
$clinic    = stripslashes($row['clinic'] ?? '');
$camp_type = stripslashes($row['type'] ?? '');

$user_outlets_edit = explode(',', $outlet);
$edit_has_access   = ($vaccine_autho == '1') || in_array($outlets, $user_outlets_edit);
$edit_allowed      = ($camp_type == '1' && $vaccine_autho == '1')
                  || ($camp_type == '2' && $edit_has_access)
                  || ($camp_type == '');

if (!$edit_allowed) { ?>
    <div class="idx-panel">
        <p style="color:#c53030 !important;font-size:13px !important;">
            <img src="../common/img/warning.png" width="16px" style="vertical-align:middle;margin-right:4px;" />
            You do not have permission to edit this campaign.
        </p>
        <a href="vaccine_campaign.php?id=<?php echo $campaign_id; ?>" class="upd-back">Back to Campaign</a>
    </div>
<?php
    $connect = 0; include('../common/index_adv.php'); exit;
}
?>

<link rel="stylesheet" href="../common/css/jquery-ui.css" type="text/css" />
<script src="../common/js/jquery-1.5.1.js"></script>
<script src="../common/js/jquery.ui.core.js"></script>
<script src="../common/js/jquery.ui.datepicker.js"></script>
<script>
function getNextElement(field) {
    var form = field.form;
    var e;
    for (e = 0; e < form.elements.length; e++) {
        if (field == form.elements[e]) { break; }
    }
    e++;
    while (form.elements[e % form.elements.length].type == "hidden") { e++; }
    return form.elements[e % form.elements.length];
}
function tabOnEnter(field, evt) {
    if (evt.keyCode === 13) {
        if (evt.preventDefault) { evt.preventDefault(); }
        else if (evt.stopPropagation) { evt.stopPropagation(); }
        else { evt.returnValue = false; }
        getNextElement(field).focus();
        return false;
    }
    return true;
}
$(function() {
    $("#v_date").datepicker({ dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true });
});
</script>

<div class="idx-panel">
    <form id="view_form" name="view_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="id" value="<?php echo $campaign_id; ?>" />
        <table class="myTable">
            <tr>
                <th>Vaccination Date <span style="color:red;">*</span></th>
                <td>
                    <input id="v_date" name="v_date" type="text" maxlength="10"
                        value="<?php echo htmlspecialchars($v_date); ?>"
                        onkeydown="return tabOnEnter(this,event)"
                        autocomplete="off" required />
                </td>
            </tr>
            <tr>
                <th>Outlet <span style="color:red;">*</span></th>
                <td>
                    <select name="outlets" id="outlets" autofocus required>
                        <?php
                        $query2  = "SELECT id, code FROM `outlet` WHERE recycle=0 ORDER BY `code`";
                        $result2 = mysqli_query($conn, $query2);
                        if ($result2) {
                            while ($row2 = $result2->fetch_assoc()) {
                                $opt_id   = stripslashes($row2['id'] ?? '');
                                $opt_code = stripslashes($row2['code'] ?? '');
                                $sel      = ($outlets == $opt_id) ? 'selected' : '';
                                echo "<option $sel value='$opt_id'>$opt_code</option>";
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Clinic <span style="color:red;">*</span></th>
                <td>
                    <select name="clinic" required>
                        <option value="">Pick One</option>
                        <?php
                        $query4  = "SELECT `id`, `dr_name`, `name` FROM `gp_clinics` WHERE is_active=1";
                        $result4 = mysqli_query($conn, $query4);
                        if ($result4) {
                            while ($nt = mysqli_fetch_assoc($result4)) {
                                $s = ($clinic == $nt['id']) ? 'selected' : '';
                                echo "<option $s value='" . htmlspecialchars($nt['id']) . "'>"
                                    . htmlspecialchars($nt['name']) . " - " . htmlspecialchars($nt['dr_name'])
                                    . "</option>";
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;margin-top:4px;">
                        <button type="submit" name="submit" id="submit" class="upd-submit">Update</button>
                        <a href="vaccine_campaign.php?id=<?php echo $campaign_id; ?>" class="upd-back">Cancel</a>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</div>
<?php
$connect = 0;
include('../common/index_adv.php');
?>
