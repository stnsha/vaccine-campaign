<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('../lock_adv.php');
$connect = 1;
include('../common/index_adv.php');

if (isset($_POST['submit'])) {
    $clinic_id = trim(mysqli_real_escape_string($conn, $_POST['clinic_id']));
    $clinic    = trim(mysqli_real_escape_string($conn, $_POST['clinic']));
    $clinic    = ucwords(strtolower($clinic));
    $c_phone   = trim(mysqli_real_escape_string($conn, $_POST['c_phone']));
    $phone_2   = trim(mysqli_real_escape_string($conn, $_POST['phone_2']));
    $email     = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $dr_name   = trim(mysqli_real_escape_string($conn, $_POST['dr_name']));
    $dr_name   = ucwords(strtolower($dr_name));
    $address   = trim(mysqli_real_escape_string($conn, $_POST['address']));

    $query2  = "SELECT count(id) as count FROM `gp_clinics` where `name`='$clinic' and `phone_1`='$c_phone' and id!='$clinic_id' and is_active=1 limit 0,1";
    $result2 = mysqli_query($conn, $query2);
    $row2    = $result2 ? $result2->fetch_assoc() : null;
    $count   = $row2 ? (int)($row2['count'] ?? 0) : 0;

    if ($count == 0) {
        $query3  = "UPDATE `gp_clinics` SET `name`='$clinic', `phone_1`='$c_phone', `phone_2`='$phone_2', `email`='$email', `dr_name`='$dr_name', `address`='$address' WHERE `gp_clinics`.`id`=$clinic_id";
        $result3 = mysqli_query($conn, $query3);
        if ($result3) {
            header("location: vaccine_index_clinic.php?s=1&id=$clinic_id");
            exit;
        }
    } else {
?>
<style type="text/css">
.idx-panel{background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:18px 22px;margin:10px 0;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;text-align:left !important;}
a.upd-back,.upd-back{display:inline-flex !important;align-items:center !important;background:#e9ecef !important;color:#111 !important;border:1px solid #d0d7de !important;border-radius:8px !important;height:32px !important;padding:5px 18px !important;font-weight:bold !important;text-decoration:none !important;font-family:Arial,Helvetica,sans-serif !important;font-size:13px !important;box-sizing:border-box !important;line-height:1 !important;}
</style>
<div class="idx-panel">
    <p style="color:#c53030 !important;font-size:13px !important;">Duplicate found — a clinic with this name and phone already exists.</p>
    <a href="javascript:history.go(-1)" class="upd-back">Go Back</a>
</div>
<?php
        $connect = 0;
        include('../common/index_adv.php');
        exit;
    }
} else {
    $clinic_id = trim(mysqli_real_escape_string($conn, $_GET['id'] ?? ''));
    $query     = "SELECT * FROM `gp_clinics` WHERE `id`='$clinic_id'";
    $result    = mysqli_query($conn, $query);
    $row       = $result ? $result->fetch_assoc() : null;
    $clinic  = $row ? stripslashes($row['name'] ?? '') : '';
    $c_phone = $row ? stripslashes($row['phone_1'] ?? '') : '';
    $phone_2 = $row ? stripslashes($row['phone_2'] ?? '') : '';
    $email   = $row ? stripslashes($row['email'] ?? '') : '';
    $dr_name = $row ? stripslashes($row['dr_name'] ?? '') : '';
    $address = $row ? stripslashes($row['address'] ?? '') : '';
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
.myTable input[type="email"],
.myTable select,
.myTable textarea {
    border-radius: 8px !important;
    padding: 5px 8px !important;
    border: 1px solid #cfcfcf !important;
    font-size: 13px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    box-sizing: border-box !important;
    background: #fff !important;
    width: 100% !important;
}
.myTable input[type="text"]:focus,
.myTable input[type="email"]:focus,
.myTable select:focus,
.myTable textarea:focus {
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
<script type="text/javascript">
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
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="form1">
        <input type="hidden" name="clinic_id" value="<?php echo $clinic_id; ?>" />
        <table class="myTable">
            <tr>
                <th>Clinic Name <span style="color:red;">*</span></th>
                <td>
                    <input type="text" id="clinic" name="clinic"
                        value="<?php echo htmlspecialchars($clinic); ?>"
                        required autofocus onkeydown="return tabOnEnter(this,event)" maxlength="255" />
                </td>
            </tr>
            <tr>
                <th>Phone 1 <span style="color:red;">*</span></th>
                <td>
                    <input type="text" id="c_phone" name="c_phone" placeholder="Phone Number"
                        value="<?php echo htmlspecialchars($c_phone); ?>"
                        autocomplete="off" onkeydown="return tabOnEnter(this,event)" maxlength="12" required />
                </td>
            </tr>
            <tr>
                <th>Phone 2</th>
                <td>
                    <input type="text" name="phone_2" placeholder="Phone Number"
                        value="<?php echo htmlspecialchars($phone_2); ?>"
                        autocomplete="off" onkeydown="return tabOnEnter(this,event)" maxlength="12" />
                </td>
            </tr>
            <tr>
                <th>Email</th>
                <td>
                    <input type="email" name="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        autocomplete="off" onkeydown="return tabOnEnter(this,event)" maxlength="25" />
                </td>
            </tr>
            <tr>
                <th>Doctor In Charge <span style="color:red;">*</span></th>
                <td>
                    <input type="text" name="dr_name"
                        value="<?php echo htmlspecialchars($dr_name); ?>"
                        onkeydown="return tabOnEnter(this,event)" required maxlength="255" />
                </td>
            </tr>
            <tr>
                <th>Address</th>
                <td>
                    <textarea name="address" style="height:80px !important;"><?php echo htmlspecialchars($address); ?></textarea>
                </td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;margin-top:6px;">
                        <button type="submit" name="submit" class="upd-submit">Update</button>
                        <a href="vaccine_index_clinic.php" class="upd-back">Cancel</a>
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
