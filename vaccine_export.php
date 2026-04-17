<?php
$connect=1;
include ('../common/index_adv.php');

$query3="SELECT `id`, `code` from `outlet` where recycle=0";
$result3=mysqli_query($conn,$query3);
$num3 = mysqli_num_rows ($result3);
$outlet_arr=array();
if ($num3 > 0 ) {
while ($row3 = $result3->fetch_assoc()) {
	$id = stripslashes($row3['id']);
	$code = stripslashes($row3['code']);
	$outlet_arr[$id]=$code;
}}

$query2="SELECT clinic, GROUP_CONCAT( DISTINCT outlet_id ORDER BY outlet_id ) AS outlets FROM vaccine_trans GROUP BY clinic";
$result2=mysqli_query($conn,$query2);
$num2 = mysqli_num_rows ($result2);
$clnic_arr=array();
if ($num2 > 0 ) {
    while ($row2 = $result2->fetch_assoc()) {
        $clinic = stripslashes($row2['clinic']);
        $outlets = stripslashes($row2['outlets']);
        $clinic_arr[$clinic]=explode(',',$outlets);
    }
}

$query="SELECT * FROM  `vaccine_clinic` WHERE `recycle`='0' order by `clinic`";
$result=mysqli_query($conn,$query);
$num = mysqli_num_rows ($result);

include ('../common/PHPexcel/PHPexcel.php');

$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);

//set a filename
$dumpfile = "clinic_list_" . date("d-m-Y_g_i_a") . ".xlsx";
//set title
$objPHPExcel->getActiveSheet()->SetCellValue('A1', "Num");
$objPHPExcel->getActiveSheet()->SetCellValue('B1', "Clinic Name");
$objPHPExcel->getActiveSheet()->SetCellValue('C1', "Contact");
$objPHPExcel->getActiveSheet()->SetCellValue('D1', "In Charge");
$objPHPExcel->getActiveSheet()->SetCellValue('E1', "Address");
$objPHPExcel->getActiveSheet()->SetCellValue('F1', "Created Since");
$objPHPExcel->getActiveSheet()->SetCellValue('G1', "Outlet");

if ($num > 0 ) {
$r=1;
$n=0;
	while ($row = $result->fetch_assoc()) {
	$timestamp = stripslashes($row['timestamp']);
	$clinic_id = stripslashes($row['id']);
	$clinic = stripslashes($row['clinic']);
	$c_phone = stripslashes($row['c_phone']);
	$dr_name = stripslashes($row['dr_name']);
	$address = stripslashes($row['address']);
		if (!empty($clinic_arr[$clinic_id]) && is_array($clinic_arr[$clinic_id])) {
			foreach ($clinic_arr[$clinic_id] as $outlet_id){
				$r++;
				$n++;
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$r, "$n");
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$r, "$clinic");
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$r, "$c_phone");
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$r, "$dr_name");
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$r, "$address");
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$r, "$timestamp");
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$r, "$outlet_arr[$outlet_id]");
			}
		}
	}
}

	$x=1;
	$j=1;
	//font-align center
	$arr=array(A,B,C,D,E,F);
	foreach ($arr as &$arr2) {
	$objPHPExcel->getActiveSheet()
		->getStyle("$arr2$x:$arr2$j")
		->getAlignment()
		->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	}

	//color cell
	function cellColor($cells,$color){
		global $objPHPExcel;

		$objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'startcolor' => array(
				 'rgb' => $color
			)
		));
	}

	//color the column title
	cellColor('A1:G1', 'a0a0ff');

	//bold title
	$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold( true );

	//autosize for all column
	foreach(range('A','G') as $columnID) {
		$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
			->setAutoSize(true);
	}

	$j=$n+1;
	//draw border
	$range2= "A1:G$j";
	//draw border
	$styleArray = array(
	  'borders' => array(
		'allborders' => array(
		  'style' => PHPExcel_Style_Border::BORDER_THIN
		)
	  )
	);
	$objPHPExcel->getActiveSheet()->getStyle("$range2")->applyFromArray($styleArray);

$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save("$dumpfile");
?>
<script type="text/javascript">
<!--
function Redirect() {
    window.location = "vaccine_download.php?id=<?php echo $dumpfile; ?>"
}
setTimeout('Redirect()', 1000);
//-->
</script>
