<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');
if($vaccine_autho!='1'){
header('location: ../permission.php');
}

if(isset($_POST['submit']))  {
$vaccine_type = trim(mysqli_real_escape_string($conn,$_POST['vaccine_type']));
$vaccine_name = trim(mysqli_real_escape_string($conn,$_POST['vaccine_name']));
$vaccine_part=explode(":",$vaccine_name);
$item_code=trim($vaccine_part[0]);
$vaccine_id = trim(mysqli_real_escape_string($conn,$_POST['id']));

//validate item_code
$query8="SELECT count(item_code) as count from simple where item_code='$item_code' limit 0,1";
$result8=mysqli_query($conn,$query8);
$row8 = $result8 -> fetch_assoc();
@$count = stripslashes($row8['count']);
if($count==0){
echo "<fieldset><div align='center'>Please select item from dropdown list!<br />
Click here to go back. --> <a href='javascript:history.go(-1)' onMouseOver='self.status=document.referrer;return true' title = Back><img src='../common/img/refresh.png'></a></div></fieldset>";
$connect=0;
include ('../common/index_adv.php');
exit;
}

//avoid empty columns
if (!$vaccine_type || !$item_code)
{
echo "<fieldset class='center'><img src='../common/img/warning.png'><br>Warning, please complete the field marked with asterisk <font color=red>*</font><br />
Click here to go back. --> <a href='javascript:history.go(-1)' onMouseOver='self.status=document.referrer;return true' title = Back><img src='../common/img/refresh.png'></a></fieldset>";
$connect=0;
include ('../common/index_adv.php');
exit;
}

//avoid same id in the system
$query3="SELECT count(id) as count_id from `vaccine_code` where `item_code` = '$item_code' and `id`!='$id' limit 0,1";
$result3=mysqli_query($conn,$query3);
$row3 = $result3 -> fetch_assoc();
@$count_id = stripslashes($row3['count_id']);
if($count_id=='0'){
$query = "UPDATE `vaccine_code` SET `vaccine_type` = '$vaccine_type', `item_code` = '$item_code' WHERE `vaccine_code`.`id` = '$vaccine_id'";
$results = mysqli_query($conn,$query);
$vaccine_id=mysqli_insert_id($conn);

if ($results)
{
header("location: vaccine_index_item.php?id=$vaccine_id");
}
} else {echo "<div  style='background-color: #ff6c44;'>Duplicates Found! </div>";}
} else {
$vaccine_id = trim(mysqli_real_escape_string($conn,$_GET['id']));
//search for content
$query3="SELECT * FROM `vaccine_code` WHERE `id`='$vaccine_id' limit 0,1";
$result3 = mysqli_query($conn, $query3);
$row3 = $result3 -> fetch_assoc();
@$vaccine_type= stripslashes($row3["vaccine_type"]);
@$item_code= stripslashes($row3["item_code"]);

//search for Description
$query4="SELECT `name` FROM `simple` WHERE `item_code`='$item_code' limit 0,1";
$result4 = mysqli_query($conn, $query4);
$row4 = $result4 -> fetch_assoc();
@$description= stripslashes($row4["name"]);
?>
<style>
	.btn {
	background-repeat: no-repeat;
	cursor:pointer;
    margin:0 2px;
    display:inline-block;
    width:18px;
    height:17px;
    background-image: url(../common/img/plus.png);
	background-size: 17px 17px;
	}
	.btn2 {
	background-repeat: no-repeat;
	cursor:pointer;
    margin:0 2px;
    display:inline-block;
    width:18px;
    height:17px;
    background-image: url(../common/img/cancel.png);
	}
	.btn3 {
	background-repeat: no-repeat;
	cursor:pointer;
    margin:0 2px;
    display:inline-block;
    width:18px;
    height:17px;
    background-image: url(../common/img/plus.png);
	background-size: 17px 17px;
	}
</style>
<script type="text/javascript" src="../common/js/jquery-1.5.1.js"></script>
<script type='text/javascript' src="../common/js/jquery.autocomplete.js"></script>
<link rel="stylesheet" type="text/css" href="../common/css/jquery.autocomplete.css" />
<script type="text/javascript" language="javascript">
(function($){
	$(document).ready(function(){
		$(".key").autocomplete("vaccine_autoCompleteMain.php", {
			width: 400,
			matchContains: true,
			selectFirst: false,
			delay: 5
		});
	})
})(jQuery);

function getNextElement(field) {
    var form = field.form;
    for ( var e = 0; e < form.elements.length; e++) {
        if (field == form.elements[e]) {
            break;
        }
    }
    e++;
	while (form.elements[e % form.elements.length].type == "hidden") {
   	 e++;
	}
	return form.elements[e % form.elements.length];
}

function tabOnEnter(field, evt) {
if (evt.keyCode === 13) {
        if (evt.preventDefault) {
            evt.preventDefault();
        } else if (evt.stopPropagation) {
            evt.stopPropagation();
        } else {
            evt.returnValue = false;
        }
        getNextElement(field).focus();
        return false;
    } else {
        return true;
    }
}

	function setFocusToTextBox(name){
		document.getElementById(name).focus();
	}
  </script>
<body>
 <div class="header" style="position: relative;">
			<b class="rtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
            <h1 class="headerH1"><img src='../common/img/library2.png'> Update Vaccine Code</h1>
            <b class="rbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
<fieldset>
	<form id="FormName" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="FormName">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td align='right'>Type <font color=red>*</font> :</td>
				<td align='left'>
					<?php
					$query4="SELECT * FROM  `vaccine_type` where recycle='0'";
					$result4 = mysqli_query($conn,$query4);

						echo "<select name='vaccine_type' required >";
						echo "<option value=''>Pick One</option>";
						while($nt=mysqli_fetch_array($result4)){
						if($nt[id]==$vaccine_type){$s='selected';} else {$s='';}
						echo "<option value='$nt[id]' $s>$nt[vaccine_name]</option>";
						}
						echo "</select>";
					?>
				</td>
			</tr>
			<tr>
				<td align='right'>New Code <font color='red'>*</font> :</td>
				<td align='left'>
					<input id='key' class="key" name="vaccine_name" placeholder="Item code or Description" maxlength='200' size='70' value='<?php echo "$item_code: $description"; ?>' onkeydown="return tabOnEnter(this,event)" required />
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="submit" name="submit" id="submit" class="form-submit" value="Update"/>
					<input type='hidden' name='id' value='<?php echo $vaccine_id; ?>' />
				</td>
			</tr>
		</table>
	</form>
</fieldset>
<?php
}
$connect=0;
include ('../common/index_adv.php');
?>
