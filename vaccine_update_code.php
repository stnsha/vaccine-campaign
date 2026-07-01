<?php
require_once('../lock_adv.php');
$connect = 1;
include('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');
if ($vaccine_autho != '1') {
    header('location: ../permission.php');
}

if (isset($_POST['submit'])) {
    $vaccine_type = trim(mysqli_real_escape_string($conn, $_POST['vaccine_type']));
    $vaccine_name = trim(mysqli_real_escape_string($conn, $_POST['vaccine_name']));
    $vaccine_part = explode(":", $vaccine_name);
    $item_code    = trim($vaccine_part[0]);
    $vaccine_id   = trim(mysqli_real_escape_string($conn, $_POST['id']));

    $query8  = "SELECT count(item_code) as count from simple where item_code='$item_code' limit 0,1";
    $result8 = mysqli_query($conn, $query8);
    $row8    = $result8->fetch_assoc();
    @$count  = stripslashes($row8['count']);
    if ($count == 0) { ?>
<style type="text/css">
.idx-panel { background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:18px 22px;margin:10px 0;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;text-align:left !important; }
a.upd-back,.upd-back{display:inline-flex !important;align-items:center !important;background:#e9ecef !important;color:#111 !important;border:1px solid #d0d7de !important;border-radius:8px !important;height:32px !important;padding:5px 18px !important;font-weight:bold !important;text-decoration:none !important;font-family:Arial,Helvetica,sans-serif !important;font-size:13px !important;box-sizing:border-box !important;line-height:1 !important;}
</style>
<div class="idx-panel">
    <p style="color:#c53030 !important;font-size:13px !important;">Please select item from the dropdown list.</p>
    <a href="javascript:history.go(-1)" class="upd-back">Go Back</a>
</div>
    <?php
    $connect = 0;
    include('../common/index_adv.php');
    exit;
    }

    if (!$vaccine_type || !$item_code) { ?>
<style type="text/css">
.idx-panel { background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:18px 22px;margin:10px 0;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;text-align:left !important; }
a.upd-back,.upd-back{display:inline-flex !important;align-items:center !important;background:#e9ecef !important;color:#111 !important;border:1px solid #d0d7de !important;border-radius:8px !important;height:32px !important;padding:5px 18px !important;font-weight:bold !important;text-decoration:none !important;font-family:Arial,Helvetica,sans-serif !important;font-size:13px !important;box-sizing:border-box !important;line-height:1 !important;}
</style>
<div class="idx-panel">
    <p style="color:#c53030 !important;font-size:13px !important;">Please complete all fields marked with <span style="color:red;">*</span></p>
    <a href="javascript:history.go(-1)" class="upd-back">Go Back</a>
</div>
    <?php
    $connect = 0;
    include('../common/index_adv.php');
    exit;
    }

    $query3   = "SELECT count(id) as count_id from `vaccine_code` where `item_code` = '$item_code' and `id`!='$vaccine_id' limit 0,1";
    $result3  = mysqli_query($conn, $query3);
    $row3     = $result3->fetch_assoc();
    @$count_id = stripslashes($row3['count_id']);
    if ($count_id == '0') {
        $query   = "UPDATE `vaccine_code` SET `vaccine_type` = '$vaccine_type', `item_code` = '$item_code' WHERE `vaccine_code`.`id` = '$vaccine_id'";
        $results = mysqli_query($conn, $query);
        if ($results) {
            header("location: vaccine_index_item.php?id=$vaccine_id");
        }
    } else { ?>
<style type="text/css">
.idx-panel { background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:18px 22px;margin:10px 0;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;text-align:left !important; }
a.upd-back,.upd-back{display:inline-flex !important;align-items:center !important;background:#e9ecef !important;color:#111 !important;border:1px solid #d0d7de !important;border-radius:8px !important;height:32px !important;padding:5px 18px !important;font-weight:bold !important;text-decoration:none !important;font-family:Arial,Helvetica,sans-serif !important;font-size:13px !important;box-sizing:border-box !important;line-height:1 !important;}
</style>
<div class="idx-panel">
    <p style="color:#c53030 !important;font-size:13px !important;">Duplicate found — this item code already exists.</p>
    <a href="javascript:history.go(-1)" class="upd-back">Go Back</a>
</div>
    <?php
    $connect = 0;
    include('../common/index_adv.php');
    exit;
    }
} else {
    $vaccine_id = trim(mysqli_real_escape_string($conn, $_GET['id']));
    $query3     = "SELECT * FROM `vaccine_code` WHERE `id`='$vaccine_id' limit 0,1";
    $result3    = mysqli_query($conn, $query3);
    $row3       = $result3->fetch_assoc();
    @$vaccine_type = stripslashes($row3["vaccine_type"]);
    @$item_code    = stripslashes($row3["item_code"]);

    $query4      = "SELECT `name` FROM `simple` WHERE `item_code`='$item_code' limit 0,1";
    $result4     = mysqli_query($conn, $query4);
    $row4        = $result4->fetch_assoc();
    @$description = stripslashes($row4["name"]);
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
}
.myTable th {
    width: 160px !important;
    font-weight: bold !important;
    text-align: left !important;
    white-space: nowrap !important;
    background: transparent !important;
    color: inherit !important;
}
.myTable td {
    text-align: left !important;
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
<script type="text/javascript" src="../common/js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="../common/js/jquery.autocomplete.js"></script>
<link rel="stylesheet" type="text/css" href="../common/css/jquery.autocomplete.css" />
<script type="text/javascript">
(function($) {
    $(document).ready(function() {
        $(".key").autocomplete("vaccine_autoCompleteMain.php", {
            width: 400,
            matchContains: true,
            selectFirst: false,
            delay: 5
        });
    });
})(jQuery);

function getNextElement(field) {
    var form = field.form;
    for (var e = 0; e < form.elements.length; e++) {
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
</script>
<div class="idx-panel">
    <form id="FormName" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName">
        <table class="myTable">
            <tr>
                <th>Type <span style="color:red;">*</span></th>
                <td>
                    <?php
                    $query4  = "SELECT * FROM `vaccine_type` where recycle='0'";
                    $result4 = mysqli_query($conn, $query4);
                    echo "<select name='vaccine_type' required style='min-width:200px;'>";
                    echo "<option value=''>Pick One</option>";
                    while ($nt = mysqli_fetch_array($result4)) {
                        $s = ($nt['id'] == $vaccine_type) ? 'selected' : '';
                        echo "<option value='$nt[id]' $s>$nt[vaccine_name]</option>";
                    }
                    echo "</select>";
                    ?>
                </td>
            </tr>
            <tr>
                <th>New Code <span style="color:red;">*</span></th>
                <td>
                    <input id="key" class="key" name="vaccine_name" type="text"
                        placeholder="Item code or Description" maxlength="200"
                        value="<?php echo htmlspecialchars("$item_code: $description"); ?>"
                        onkeydown="return tabOnEnter(this, event)" required style="width:100%;" />
                </td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;margin-top:6px;">
                        <button type="submit" name="submit" id="submit" class="upd-submit">Update</button>
                        <a href="vaccine_index_item.php" class="upd-back">Cancel</a>
                        <input type="hidden" name="id" value="<?php echo $vaccine_id; ?>" />
                    </div>
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
