<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<style type="text/css">
/* calendar */
td.calendar-day  {
	background:#FFFFFF;
}

td.calendar-day:hover  {
	background:#EEEEF2;
}

td.calendar-day  {
	min-height:80px;
	position:relative;
}

td.calendar-day-head {
	background:#9999FF;
	font-weight:bold;
	text-align:center;
	width:120px;
	padding:5px;
	border-right:1px solid #EFEFEF;
}

/* shared */
div.day-number   {
	background:#CCCCFF;
	position:absolute;
	z-index:2;
	top:0px;
	right:0px;
	padding:5px;
	font-weight:bold;
	width:16px;
	text-align:center;
}
a.day-number   {
	text-decoration:none;
	color:#000000;
}

td.calendar-day, td.calendar-day-np {
	vertical-align:top;
	width:130px;
	height:100px;
	padding:5px 25px 5px 5px;
	border-bottom:1px solid #EFEFEF;
	border-right:1px solid #EFEFEF;
}

td.calendar-day-np {
	background:#f3f3f3;
}

/* tooltip */
a.tooltip {
	text-decoration:none;
}

.tooltip span {
	margin-left: -999em;
	position: absolute;
}
.tooltip:hover span {
	border-radius: 5px 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px;
	box-shadow: 5px 5px 5px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 5px 5px rgba(0, 0, 0, 0.1); -moz-box-shadow: 5px 5px rgba(0, 0, 0, 0.1);
	font-family: Calibri, Tahoma, Geneva, sans-serif;
	position: absolute; left: 1em; top: 2em; z-index: 99;
	margin-left: 0; width: 250px;
}
.tooltip:hover img {
	border: 0; margin: -10px 0 0 -55px;
	float: left; position: absolute;
}
.tooltip:hover em {
	font-family: Candara, Tahoma, Geneva, sans-serif; font-size: 1.2em; font-weight: bold;
	display: block; padding: 0.2em 0 0.6em 0;
}
.classic { padding: 0.8em 1em; }
.classic {background: #FFFFAA; border: 1px solid #FFAD33; }
</style>
<?php
require_once('../lock_adv.php');
$connect=1;
include ('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');

// Check user permissions and get accessible outlets
if($vaccine_autho=='1') {
    // Admin: can view all outlets
    $query_user_outlets = "SELECT id FROM outlet WHERE recycle='0' ORDER BY code";
    $result_user_outlets = mysqli_query($conn, $query_user_outlets);
    $num_user_outlets = mysqli_num_rows($result_user_outlets);

    $user_outlet_list = '';
    $user_outlet_array = array();
    if($num_user_outlets > 0) {
        $i_outlet = 0;
        while($row_outlet = $result_user_outlets->fetch_assoc()) {
            $o_id = stripslashes($row_outlet['id']);
            if($i_outlet == 0) {
                $user_outlet_list .= "$o_id";
            } else {
                $user_outlet_list .= ",$o_id";
            }
            $user_outlet_array[] = $o_id;
            $i_outlet++;
        }
    }
} else {
    // Regular staff: only their assigned outlet(s)
    $query_user_outlets = "SELECT outlet FROM staff WHERE id='$id_user'";
    $result_user_outlets = mysqli_query($conn, $query_user_outlets);
    $row_user_outlets = $result_user_outlets->fetch_assoc();
    $user_outlet_list = stripslashes($row_user_outlets['outlet']);
    $user_outlet_array = explode(",", $user_outlet_list);
}

// Handle outlet selection
$outlet_selected = trim(mysqli_real_escape_string($conn, $_REQUEST['outlet_selected']));

// Validate that selected outlet is in user's accessible outlets
if($outlet_selected != '') {
    if(in_array($outlet_selected, $user_outlet_array)) {
        $option = "and vc.`outlets`='$outlet_selected'";
    } else {
        $option = "and vc.`outlets` IN ($user_outlet_list)";
        $outlet_selected = '';
    }
} else {
    $option = "and vc.`outlets` IN ($user_outlet_list)";
}

/* draws a calendar */
function draw_calendar($month, $year, $vaccine_campaigns = array(), $conn) {

    $calendar = '<table cellpadding="0" cellspacing="0" class="calendar" width="100%">';

    $headings = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
    $calendar.= '<tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">', $headings).'</td></tr>';

    $running_day      = date('w', mktime(0,0,0,$month,1,$year));
    $days_in_month    = date('t', mktime(0,0,0,$month,1,$year));
    $days_in_this_week = 1;
    $day_counter      = 0;

    $calendar.= '<tr class="calendar-row">';

    for($x = 0; $x < $running_day; $x++):
        $calendar.= '<td class="calendar-day-np">&nbsp;</td>';
        $days_in_this_week++;
    endfor;

    $today = date("Y-m-d");

    for($list_day = 1; $list_day <= $days_in_month; $list_day++):
        $calendar.= '<td class="calendar-day"><div>';

        if($list_day < 10) {
            $list_day = str_pad($list_day, 2, '0', STR_PAD_LEFT);
        }

        $vaccine_day = $year.'-'.$month.'-'.$list_day;

        // Only allow adding campaigns for today or future dates
        if($vaccine_day >= $today) {
            $calendar.= '<div class="day-number"><a class="day-number" href="vaccine_add_campaign.php?v_date='.$vaccine_day.'" title="Add New Campaign">'.$list_day.'</a></div>';
        } else {
            $calendar.= '<div class="day-number" style="background:#CCCCCC; color:#666666; cursor:not-allowed;" title="Cannot add campaign for past dates">'.$list_day.'</div>';
        }

        if($vaccine_day == $today) {
            $calendar.= '<div align=center><img src="../common/img/smiley.png" width="12px"> <b><u>Today</u></b></div>';
        }

        if(isset($vaccine_campaigns[$vaccine_day])) {
            foreach($vaccine_campaigns[$vaccine_day] as $campaign) {
                $campaign_id      = $campaign['id'];
                $outlet_code      = $campaign['outlet_code'];
                $clinic           = $campaign['clinic'];
                $dr_name          = $campaign['dr_name'];
                $customer_count   = $campaign['customer_count'];
                $vaccinated_count = $campaign['vaccinated_count'];
                $camp_type        = isset($campaign['type'])   ? $campaign['type']   : '1';
                $camp_status      = isset($campaign['status']) ? $campaign['status'] : '1';

                $days_until = (strtotime($vaccine_day) - strtotime($today)) / 86400;

                // Status icon and text
                if($camp_status == '2') {
                    $status_text  = 'Cancelled';
                    $display_icon = '<img src="../common/img/bclose.png" width="12px"> ';
                } else if($camp_status == '0') {
                    $status_text  = 'Waiting for Outlet Acknowledgement';
                    $display_icon = '<img src="../common/img/personal_exp.png" width="12px"> ';
                } else {
                    if($days_until > 0) {
                        $status_text  = 'Upcoming';
                        $display_icon = '<img src="../common/img/tick.png" width="12px"> ';
                    } else if(round($days_until) == 0) {
                        $status_text  = 'Today is the Vaccine Event';
                        $display_icon = '<img src="../common/img/tick.png" width="12px"> ';
                    } else {
                        $status_text  = 'Completed';
                        $display_icon = '<img src="../common/img/tick.png" width="12px" style="filter:grayscale(100%)"> ';
                    }
                }

                // Type badge
                if($camp_type == '1') {
                    $type_badge  = '<span style="background:#3366FF; color:white; padding:2px 5px; border-radius:3px; font-size:9px; font-weight:bold;">HQ</span> ';
                    $type_label  = 'HQ Initiated';
                } else {
                    $type_badge  = '<span style="background:#FF6600; color:white; padding:2px 5px; border-radius:3px; font-size:9px; font-weight:bold;">Outlet</span> ';
                    $type_label  = 'Outlet Initiated';
                }

                $display_text = $type_badge . $outlet_code . ' (' . $customer_count . ')';
                if($camp_status == '2') {
                    $display_text = $type_badge . '<strike>' . $outlet_code . ' (' . $customer_count . ')</strike>';
                }

                $tooltip  = '<span class="classic">';
                $tooltip .= 'Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;'.$vaccine_day.'<br/>';
                $tooltip .= 'Type&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;'.$type_label.'<br/>';
                $tooltip .= 'Outlet&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;'.$outlet_code.'<br/>';
                $tooltip .= 'Clinic&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;'.$clinic.'<br/>';
                $tooltip .= 'Doctor&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;'.$dr_name.'<br/>';
                $tooltip .= 'Booked&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;'.$customer_count.'<br/>';
                $tooltip .= 'Vaccinated&nbsp;:&nbsp;'.$vaccinated_count.'<br/>';
                $tooltip .= 'Status&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;'.$status_text;
                $tooltip .= '</span>';

                $calendar.= '<p>'.$display_icon.'<a class="tooltip" href="vaccine_campaign.php?id='.$campaign_id.'">'.$display_text.$tooltip.'</a></p>';
            }
        } else {
            $calendar.= str_repeat('<p>&nbsp;</p>', 5);
        }

        $calendar.= '</div></td>';

        if($running_day == 6):
            $calendar.= '</tr>';
            if(($day_counter+1) != $days_in_month):
                $calendar.= '<tr class="calendar-row">';
            endif;
            $running_day = -1;
            $days_in_this_week = 0;
        endif;
        $days_in_this_week++; $running_day++; $day_counter++;
    endfor;

    if($days_in_this_week < 8):
        for($x = 1; $x <= (8 - $days_in_this_week); $x++):
            $calendar.= '<td class="calendar-day-np">&nbsp;</td>';
        endfor;
    endif;

    $calendar.= '</tr>';
    $calendar.= '</table>';

    $calendar = str_replace('</td>', '</td>'."\n", $calendar);
    $calendar = str_replace('</tr>', '</tr>'."\n", $calendar);

    return $calendar;
}

/* date settings */
$month = (int) ($_REQUEST['month'] ? $_REQUEST['month'] : date('m'));
$year  = (int)  ($_REQUEST['year']  ? $_REQUEST['year']  : date('Y'));

/* select month control */
$select_month_control = '<select name="month" id="month" onchange="submit()">';
for($x = 1; $x <= 12; $x++) {
    $select_month_control.= '<option value="'.$x.'"'.($x != $month ? '' : ' selected="selected"').'>'.date('F', mktime(0,0,0,$x,1,$year)).'</option>';
}
$select_month_control.= '</select>';

/* select year control */
$year_range = 7;
$select_year_control = '<select name="year" id="year" onchange="submit()">';
for($x = ($year - floor($year_range/2)); $x <= ($year + floor($year_range/2)); $x++) {
    $select_year_control.= '<option value="'.$x.'"'.($x != $year ? '' : ' selected="selected"').'>'.$x.'</option>';
}
$select_year_control.= '</select>';

/* navigation controls */
$next_month_link   = '<a href="?month='.($month != 12 ? $month + 1 : 1).'&year='.($month != 12 ? $year : $year + 1).'&outlet_selected='.$outlet_selected.'" class="control"><img src="../common/img/front.png" title="Next Month"></a>';
$previous_month_link = '<a href="?month='.($month != 1 ? $month - 1 : 12).'&year='.($month != 1 ? $year : $year - 1).'&outlet_selected='.$outlet_selected.'" class="control"><img src="../common/img/backward.png" title="Previous Month"></a>';
$next_year         = '<a href="?month='.$month.'&year='.($year + 1).'&outlet_selected='.$outlet_selected.'" class="control"><img src="../common/img/fast_front.png" title="Next Year"></a>';
$previous_year     = '<a href="?month='.$month.'&year='.($year - 1).'&outlet_selected='.$outlet_selected.'" class="control"><img src="../common/img/fast_back.png" title="Previous Year"></a>';

$controls = '<form method="get" action='.$_SERVER['PHP_SELF'].'>'.$select_month_control.$select_year_control.'&nbsp;</form>';

/* get all vaccine campaigns for the given month */
$month = str_pad($month, 2, '0', STR_PAD_LEFT);
$vaccine_campaigns = array();
$query = "SELECT vc.id,
          vc.v_date,
          vc.outlets AS outlet_id,
          vc.type,
          vc.status,
          o.code AS outlet_code,
          vcl.clinic,
          vcl.dr_name,
          COUNT(vt.id) AS customer_count,
          SUM(CASE WHEN vt.status = 1 THEN 1 ELSE 0 END) AS vaccinated_count
          FROM vaccine_campaign vc
          LEFT JOIN outlet o ON vc.outlets = o.id
          LEFT JOIN vaccine_clinic vcl ON vc.clinic = vcl.id
          LEFT JOIN vaccine_trans vt ON vt.outlet_id = vc.outlets AND DATE(vt.v_date) = vc.v_date AND vt.recycle = 0
          WHERE vc.v_date >= '$year-$month-01'
            AND vc.v_date < DATE_ADD('$year-$month-01', INTERVAL 1 MONTH)
            $option
          GROUP BY vc.id, vc.v_date, vc.outlets, vc.type, vc.status, o.code, vcl.clinic, vcl.dr_name
          ORDER BY vc.v_date";
$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
while($row = mysqli_fetch_assoc($result)) {
    $vaccine_campaigns[$row['v_date']][] = $row;
}
?>
<div class='header' style='position: relative;'>
    <b class='rtop'><b class='r1'></b><b class='r2'></b><b class='r3'></b><b class='r4'></b></b>
    <h1 class='headerH1'><img src='../common/img/vaccine.png' width='22px'> Vaccine Campaign Calendar</h1>
    <b class='rbottom'><b class='r4'></b><b class='r3'></b><b class='r2'></b><b class='r1'></b></b>
</div>
<fieldset>
<table cellspacing='0' border='0' width='100%'>
    <tr>
        <td width='6%' align='right'><?php echo "$previous_year$previous_month_link"; ?></td>
        <td width='15%' align='center'><?php echo $controls; ?></td>
        <td><?php echo "$next_month_link$next_year"; ?></td>
        <td align='right'>
            <form method="get" action='<?php echo $_SERVER['PHP_SELF']; ?>'>
                <?php
                $outlet_ids_for_dropdown = implode(',', $user_outlet_array);
                $query_outlet = "SELECT id, code FROM `outlet` WHERE recycle='0' AND id IN ($outlet_ids_for_dropdown) ORDER BY code";
                $result_outlet = mysqli_query($conn, $query_outlet);

                echo "<select id='outlet_selected' name='outlet_selected' onchange='submit();'>";
                if($vaccine_autho == '1') {
                    echo "<option value=''>All Outlets</option>";
                } else {
                    echo "<option value=''>All My Outlets</option>";
                }
                while($nt = mysqli_fetch_array($result_outlet)) {
                    if($outlet_selected == $nt['id']) { $s = "selected"; } else { $s = ""; }
                    echo "<option value='$nt[id]' $s>$nt[code]</option>";
                }
                echo "</select>";
                ?>
                <input type='hidden' name='month' value='<?php echo $month; ?>' />
                <input type='hidden' name='year' value='<?php echo $year; ?>' />
            </form>
        </td>
    </tr>
</table>
<?php
echo draw_calendar($month, $year, $vaccine_campaigns, $conn);
?>

<!-- Legend Section -->
<div style="margin-top: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">
    <h3 style="margin-top: 0; color: #333;">Legend</h3>
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">

        <div style="flex: 1; min-width: 250px;">
            <h4 style="color: #666; margin-bottom: 10px;">Campaign Types:</h4>
            <table cellpadding="5" cellspacing="0" style="width: 100%;">
                <tr>
                    <td width="70px"><span style="background:#3366FF; color:white; padding:2px 6px; border-radius:3px; font-size:10px; font-weight:bold;">HQ</span></td>
                    <td>HQ Initiated Campaign</td>
                </tr>
                <tr>
                    <td colspan="2" style="font-size: 11px; color: #666; padding-left: 10px;">
                        Requires outlet acknowledgement. Only HQ can edit or cancel.
                    </td>
                </tr>
                <tr><td colspan="2" style="height: 8px;"></td></tr>
                <tr>
                    <td><span style="background:#FF6600; color:white; padding:2px 6px; border-radius:3px; font-size:10px; font-weight:bold;">Outlet</span></td>
                    <td>Outlet Initiated Campaign</td>
                </tr>
                <tr>
                    <td colspan="2" style="font-size: 11px; color: #666; padding-left: 10px;">
                        Auto-acknowledged. Outlet or HQ can edit and cancel.
                    </td>
                </tr>
                <tr><td colspan="2" style="height: 6px;"></td></tr>
                <tr>
                    <td colspan="2" style="font-size: 11px; color: #555;">
                        Format: <b>Outlet (Vaccinated / Total Booked)</b>
                    </td>
                </tr>
            </table>
        </div>

        <div style="flex: 1; min-width: 300px;">
            <h4 style="color: #666; margin-bottom: 10px;">Status Icons:</h4>
            <table cellpadding="5" cellspacing="0" style="width: 100%;">
                <tr>
                    <td width="30px"><img src="../common/img/personal_exp.png" width="16px"></td>
                    <td><b>Waiting for Outlet Acknowledgement</b><br/>
                        <span style="font-size: 11px; color: #666;">HQ campaign pending outlet confirmation</span>
                    </td>
                </tr>
                <tr><td colspan="2" style="height: 8px;"></td></tr>
                <tr>
                    <td><img src="../common/img/tick.png" width="16px"></td>
                    <td><b>Upcoming / Today</b><br/>
                        <span style="font-size: 11px; color: #666;">Acknowledged, event on today or future date</span>
                    </td>
                </tr>
                <tr><td colspan="2" style="height: 8px;"></td></tr>
                <tr>
                    <td><img src="../common/img/tick.png" width="16px" style="filter:grayscale(100%)"></td>
                    <td><b>Completed</b><br/>
                        <span style="font-size: 11px; color: #666;">Campaign date has passed</span>
                    </td>
                </tr>
                <tr><td colspan="2" style="height: 8px;"></td></tr>
                <tr>
                    <td><img src="../common/img/bclose.png" width="16px"></td>
                    <td><b>Cancelled</b><br/>
                        <span style="font-size: 11px; color: #666;">Campaign has been cancelled (shown with strikethrough)</span>
                    </td>
                </tr>
                <tr><td colspan="2" style="height: 8px;"></td></tr>
                <tr>
                    <td><img src="../common/img/smiley.png" width="16px"></td>
                    <td><b>Today</b><br/>
                        <span style="font-size: 11px; color: #666;">Current date marker</span>
                    </td>
                </tr>
            </table>
        </div>

        <div style="flex: 1; min-width: 200px;">
            <h4 style="color: #666; margin-bottom: 10px;">How to Use:</h4>
            <table cellpadding="5" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="font-size: 11px; color: #555;">
                        Click on a <b>date number</b> (today or future) to add a new vaccine campaign for that date.<br/><br/>
                        Click on an <b>existing campaign entry</b> to edit it.
                    </td>
                </tr>
            </table>
        </div>

    </div>
</div>

<?php
echo "</fieldset>";
$connect = "0";
include('../common/index_adv.php');
?>
