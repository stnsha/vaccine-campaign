<meta http-equiv="Content-type" content="text/html; charset=iso-8859-1">
<style>
@-webkit-keyframes fadeInOut {
    0% {
        opacity: 0;
    }
    16% {
        opacity: 1;
    }
    84% {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
}
@keyframes fadeInOut {
    0% {
        opacity: 0;
    }
    16% {
        opacity: 1;
    }
    84% {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
}
.message {
    width: 400px;
    margin: 0 auto;
    opacity: 0;
    text-align: center;
    -webkit-animation: fadeInOut 2s;
    animation: fadeInOut 2s;
}
</style>
<?php 
//connect to database
$connect=1;
include ('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur'); 

//for vip card
$ic=trim(mysqli_real_escape_string($conn,$_GET['ic']));
if($ic!=''){
$name=trim(mysqli_real_escape_string($conn,$_GET['name'])); //vip

//search for same ic and name, how many result found
$query = "SELECT id, customer_name, phone, ic  FROM  `customer` WHERE `ic`='$ic' and `customer_name`='$name' and `recycle`='0'";
$result = mysqli_query($conn,$query) or die(mysqli_error($conn));
$num = mysqli_num_rows ($result);
$row = $result -> fetch_assoc();
@$id = stripslashes($row['id']);
@$phone = stripslashes($row['phone']);
$phone_parts = explode("@",$phone);
@$customer_name = stripslashes($row['customer_name']);

if ($result)
{
echo "<img src='../common/img/tick.png' title='Ok'>|$phone_parts[0]|$ic|$customer_name|$phone_parts[1]";
} 
}

//search for campaign and clinic
$outlet_id=trim(mysqli_real_escape_string($conn,$_GET['id']));
if($outlet_id){
	$query = "SELECT `vaccine_campaign`.`id`, `v_date`, `vaccine_clinic`.`clinic`, `dr_name` FROM `vaccine_campaign` left join `vaccine_clinic` on `vaccine_campaign`.`clinic`=`vaccine_clinic`.`id` where v_date >= '".date('Y-m-d')."' and `outlets`='$outlet_id' order by `v_date`";
	$result = mysqli_query($conn,$query) or die(mysqli_error($conn));
	$num=mysqli_num_rows($result);
	if($num==1){$v='selected';} else {$v='';}
	
	$dropdown_date.= "<select name='campaign' required>";
	if($num>0){
		if($num>1){$dropdown_date.="<option>Pick One</option>";}
		while ($row = $result->fetch_assoc()) {
			$id = stripslashes($row['id']);
			$v_date = stripslashes($row['v_date']);
			$clinic = stripslashes($row['clinic']);
			$dr_name = stripslashes($row['dr_name']);
			$dropdown_date.= "<option value='$id' $v>$v_date - $clinic ($dr_name)</option>";
		}
	} else {
		$dropdown_date.="<option value=''>No active campaign found!</option>";
	}
	$dropdown_date.="</select>";
	echo "$dropdown_date";
}

$vaccine_type=trim(mysqli_real_escape_string($conn,$_GET['vt']));
if($vaccine_type){
	$query2="select `item_code` from `vaccine_code` where `vaccine_type`='$vaccine_type'";
	$result2 = mysqli_query($conn, $query2);
	$num2=mysqli_num_rows($result2);
	$dropdown_item.="<select name='item_code' required onchange='setFocusToTextBox(\"batch_num\")'><option value=''>Pick One</option>";
	if($num2>0){
		while ($row2 = $result2->fetch_assoc()) {
			$item_code = stripslashes($row2['item_code']);
				// search for item code description
				$query3="SELECT `name` from `simple` where `item_code`='$item_code' limit 0,1";
				$result3 = mysqli_query($conn, $query3);
				$num3=mysqli_num_rows($result3);
				$row3 = $result3 -> fetch_assoc();
				@$description= stripslashes($row3["name"]);
			$dropdown_item.="<option value='$item_code'>$item_code: $description</option>";
		}
	}
	$dropdown_item.="</select>";
	echo $dropdown_item;
}


//update data
$column_name=trim(mysqli_real_escape_string($conn,$_GET['n']));
if($column_name){
$column_value=trim(mysqli_real_escape_string($conn,$_GET['v']));
$trans_id=trim(mysqli_real_escape_string($conn,$_GET['t_id']));
$query="update vaccine_trans set `$column_name`='$column_value' where id='$trans_id'";
$result=mysqli_query($conn, $query);
echo "<span class='message'><img src='../common/img/save.png'></span>";
}
?>
