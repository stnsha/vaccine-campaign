<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
require_once('../lock_adv.php');
$connect=1;
include('../common/index_adv.php');

$prefill_date = (isset($_GET['v_date']) && $_GET['v_date'] != '') ? trim($_GET['v_date']) : date("Y-m-d");
?>
<link rel="stylesheet" href="../common/css/jquery-ui.css" type="text/css">
<script src="../common/js/jquery-1.5.1.js"></script>
<script src="../common/js/jquery.ui.core.js"></script>
<script src="../common/js/jquery.ui.datepicker.js"></script>
<script type='text/javascript' src="../common/js/jquery.autocomplete.js"></script>
<link rel="stylesheet" type="text/css" href="../common/css/jquery.autocomplete.css" />
<script>
$().ready(function() {
    $( "#v_date" ).datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true});

    $("#outlet_search").autocomplete("vaccine_autoComplete_outlet.php", {
        width: 300,
        matchContains: true,
        selectFirst: false,
        delay: 10
    });
    $("#outlet_search").result(function(event, data, formatted) {
        var val = document.getElementById("outlet_search").value;
        var parts = val.split(':');
        document.getElementById("outlet_hidden").value = parts[0].trim();
    });

    $("#clinic_search").autocomplete("vaccine_autoComplete.php", {
        width: 450,
        matchContains: true,
        selectFirst: false,
        delay: 10
    });
    $("#clinic_search").result(function(event, data, formatted) {
        var val = document.getElementById("clinic_search").value;
        var parts = val.split(':');
        document.getElementById("clinic_hidden").value = parts[0].trim();
    });
});

function validateForm() {
    if(!document.getElementById("outlet_hidden").value) {
        alert("Please select an outlet from the dropdown list.");
        return false;
    }
    if(!document.getElementById("clinic_hidden").value) {
        alert("Please select a clinic from the dropdown list.");
        return false;
    }
    return true;
}
</script>
<div class="header"><b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
             <h1 class="headerH1"><img src='../common/img/vaccine.png'> Add New Campaign </h1>
             <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<div align='left' style='margin-bottom:8px;'><a href="vaccine_calendar.php">Back to Calendar</a></div>
<fieldset>
<form action="vaccine_save_campaign.php" method="post" name="form1" onsubmit="return validateForm()">
<table>
    <tr>
        <th style="background-color:#9999ff" width="150">
            <div align='right'>Vaccination Date<font color='red'>*</font> :</div>
        </th>
        <td>
            <input id="v_date" name="v_date" type="text" maxlength="10" value='<?php echo $prefill_date; ?>' size='10' autocomplete='off' required />
        </td>
    </tr>
    <tr>
        <th style="background-color:#9999ff">
            <div align='right'>Outlet<font color='red'>*</font> :</div>
        </th>
        <td>
            <input id="outlet_search" placeholder="Outlet Code" size='20' autocomplete="off" />
            <input type="hidden" id="outlet_hidden" name="outlet[]" />
        </td>
    </tr>
    <tr>
        <th style="background-color:#9999ff">
            <div align='right'>Clinic<font color='red'>*</font> :</div>
        </th>
        <td>
            <input id="clinic_search" placeholder="Clinic Name" size='50' autocomplete="off" />
            <input type="hidden" id="clinic_hidden" name="clinic[]" />
        </td>
    </tr>
    <tr>
        <th style="background-color:#9999ff"></th>
        <td>
            <input type="submit" name="submit" value="Submit" class="form-submit" />
            &nbsp;<a href="vaccine_calendar.php">Cancel</a>
        </td>
    </tr>
</table>
</form>
</fieldset>
<?php
$connect=0;
include('../common/index_adv.php');
?>
