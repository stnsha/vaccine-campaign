<?php
require_once('vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

ob_start();

require_once('../lock_adv.php');
$connect = 1;
include('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');

if($vaccine_autho != '1') {
    ob_end_clean();
    header('Location: vaccine_campaign_summary.php');
    exit;
}

$date_from     = isset($_GET['date_from'])     ? trim(mysqli_real_escape_string($conn, $_GET['date_from']))     : date('Y-m-d');
$date_to       = isset($_GET['date_to'])       ? trim(mysqli_real_escape_string($conn, $_GET['date_to']))       : date('Y-m-d');
$filter_type   = isset($_GET['filter_type'])   ? trim(mysqli_real_escape_string($conn, $_GET['filter_type']))   : '';
$filter_outlet = isset($_GET['filter_outlet']) ? trim(mysqli_real_escape_string($conn, $_GET['filter_outlet'])) : '';

if($date_to < $date_from) {
    $date_to = $date_from;
}

$where = array("vc.v_date BETWEEN '$date_from' AND '$date_to'", "vc.status != '2'");
if($filter_type != '') {
    $where[] = "vc.type='" . $filter_type . "'";
}
if($filter_outlet != '') {
    $where[] = "vc.outlets='" . $filter_outlet . "'";
}
$where_sql = implode(' AND ', $where);

$query = "SELECT vc.id, vc.v_date, vc.type, vc.status, vc.outlets AS outlet_id,
                 o.code AS outlet_code, o.comp_name AS outlet_name,
                 cl.name AS clinic_name, cl.dr_name
          FROM vaccine_campaign vc
          LEFT JOIN outlet o ON vc.outlets = o.id
          LEFT JOIN gp_clinics cl ON vc.clinic = cl.id
          WHERE $where_sql
          ORDER BY vc.v_date ASC, o.code ASC";

$result = mysqli_query($conn, $query);

// Pre-populate booked/vaccinated counts for the whole filtered date range in one query,
// grouped by outlet+date, instead of a correlated subquery per campaign row.
$trans_counts = array();
$counts_query = "SELECT outlet_id, DATE(v_date) AS d,
                         COUNT(*) AS booked,
                         SUM(CASE WHEN status = '1' THEN 1 ELSE 0 END) AS vaccinated
                  FROM vaccine_trans
                  WHERE v_date >= '$date_from' AND v_date < '$date_to' + INTERVAL 1 DAY AND recycle = 0
                  GROUP BY outlet_id, DATE(v_date)";
$counts_result = mysqli_query($conn, $counts_query);
if ($counts_result) {
    while ($crow = $counts_result->fetch_assoc()) {
        $trans_counts[$crow['outlet_id'] . '|' . $crow['d']] = array(
            'booked'     => (int)$crow['booked'],
            'vaccinated' => (int)$crow['vaccinated']
        );
    }
}

ini_set('memory_limit', '512M');
ini_set('max_execution_time', 120);

$objPHPExcel = new Spreadsheet();
$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();
$sheet->setTitle('Vaccine Campaign Summary');

$headers     = array('No.', 'Vaccination Date', 'Day', 'Outlet Code', 'Outlet Name', 'Campaign Type', 'Clinic', 'Doctor', 'Status', 'Booked', 'Vaccinated');
$col_letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K');

foreach($headers as $i => $header) {
    $sheet->setCellValue($col_letters[$i] . '1', $header);
}

$sheet->getStyle('A1:K1')->getFont()->setBold(true);

$sheet->setCellValue('A2', 'Period: ' . date('d M Y', strtotime($date_from)) . ' to ' . date('d M Y', strtotime($date_to)));
$sheet->mergeCells('A2:K2');
$sheet->getStyle('A2')->getFont()->setItalic(true);
$sheet->getStyle('A2')->getFont()->setSize(10);
$sheet->getStyle('A2')->getFont()->getColor()->setRGB('666666');

$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(16);
$sheet->getColumnDimension('C')->setWidth(12);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(28);
$sheet->getColumnDimension('F')->setWidth(16);
$sheet->getColumnDimension('G')->setWidth(28);
$sheet->getColumnDimension('H')->setWidth(20);
$sheet->getColumnDimension('I')->setWidth(24);
$sheet->getColumnDimension('J')->setWidth(10);
$sheet->getColumnDimension('K')->setWidth(10);

$row_num = 3;
$no      = 1;

if($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $camp_date = $row['v_date'];
        $day_name  = date('l', strtotime($camp_date));
        $camp_type = $row['type'];

        $type_disp = ($camp_type == '1') ? 'HQ Initiated' : 'Outlet Initiated';

        $clinic_disp = !empty($row['clinic_name']) ? $row['clinic_name'] : '';
        $doctor_disp = !empty($row['dr_name']) ? $row['dr_name'] : '';

        if($row['status'] == '0') {
            $status_disp = 'Pending Acknowledgement';
        } else if($row['status'] == '1') {
            $days_left = (strtotime($camp_date) - strtotime(date('Y-m-d'))) / 86400;
            if($days_left > 0) {
                $status_disp = 'Recruiting';
            } else if(round($days_left) == 0) {
                $status_disp = 'Today is the Vaccine Event';
            } else {
                $status_disp = 'Completed';
            }
        } else {
            $status_disp = 'Cancelled';
        }

        $sheet->setCellValue('A' . $row_num, $no);
        $sheet->setCellValue('B' . $row_num, date('d/m/Y', strtotime($camp_date)));
        $sheet->setCellValue('C' . $row_num, $day_name);
        $sheet->setCellValue('D' . $row_num, $row['outlet_code']);
        $sheet->setCellValue('E' . $row_num, $row['outlet_name']);
        $sheet->setCellValue('F' . $row_num, $type_disp);
        $count_key = ($row['outlet_id'] ?? '') . '|' . $camp_date;
        $total_booked     = isset($trans_counts[$count_key]) ? $trans_counts[$count_key]['booked'] : 0;
        $total_vaccinated = isset($trans_counts[$count_key]) ? $trans_counts[$count_key]['vaccinated'] : 0;

        $sheet->setCellValue('G' . $row_num, $clinic_disp);
        $sheet->setCellValue('H' . $row_num, $doctor_disp);
        $sheet->setCellValue('I' . $row_num, $status_disp);
        $sheet->setCellValue('J' . $row_num, $total_booked);
        $sheet->setCellValue('K' . $row_num, $total_vaccinated);

        if($day_name == 'Saturday' || $day_name == 'Sunday') {
            $sheet->getStyle('A' . $row_num . ':K' . $row_num)->getFill()->applyFromArray(array(
                'type'       => Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'FFFDE7')
            ));
        }

        $row_num++;
        $no++;
    }
}

mysqli_close($conn);

ob_end_clean();

$filename = 'vaccine_campaign_summary_' . $date_from . '_to_' . $date_to . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
$objWriter->save('php://output');
exit;
?>
