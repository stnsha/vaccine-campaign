<?php
require_once('../lock_adv.php');
$connect = 1;
include('../common/index_adv.php');
date_default_timezone_set('Asia/Kuala_Lumpur');

if ($vaccine_autho != '1') {
    header('Location: vaccine_calendar.php');
    exit;
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'form';

$date_from = '2026-08-01';
$date_to   = '2026-12-31';

// Finds outlet+date groups with more than one campaign row in the given range,
// then classifies each group: delete the waiting-ack row only when exactly one
// acknowledged row exists alongside it in the same group (same outlet, same date).
function vc_dedupe_find_groups($conn, $date_from, $date_to) {
    $groups = array();

    $dup_query = "SELECT outlets, v_date, COUNT(*) AS cnt
                  FROM vaccine_campaign
                  WHERE v_date BETWEEN '$date_from' AND '$date_to'
                  GROUP BY outlets, v_date
                  HAVING COUNT(*) > 1";
    $dup_result = mysqli_query($conn, $dup_query);
    if (!$dup_result) {
        return $groups;
    }

    while ($dup_row = $dup_result->fetch_assoc()) {
        $outlet_id = $dup_row['outlets'];
        $v_date    = $dup_row['v_date'];

        $rows_query = "SELECT id, status, type, clinic
                       FROM vaccine_campaign
                       WHERE outlets='$outlet_id' AND v_date='$v_date'
                       ORDER BY id";
        $rows_result = mysqli_query($conn, $rows_query);
        $rows = array();
        if ($rows_result) {
            while ($r = $rows_result->fetch_assoc()) {
                $rows[] = $r;
            }
        }

        $acknowledged = array();
        $waiting      = array();
        $other        = array();
        foreach ($rows as $r) {
            if ($r['status'] == '1') {
                $acknowledged[] = $r;
            } elseif ($r['status'] == '0') {
                $waiting[] = $r;
            } else {
                $other[] = $r;
            }
        }

        $with_clinic    = array();
        $without_clinic = array();
        foreach ($waiting as $w) {
            if (!empty($w['clinic']) && $w['clinic'] != '0') {
                $with_clinic[] = $w;
            } else {
                $without_clinic[] = $w;
            }
        }

        if (count($acknowledged) == 1 && count($waiting) >= 1 && count($other) == 0) {
            $decision   = 'delete';
            $reason     = 'Exactly one acknowledged row found; removing waiting-ack duplicate(s).';
            $delete_ids = array();
            foreach ($waiting as $w) {
                $delete_ids[] = $w['id'];
            }
            $keep_id = $acknowledged[0]['id'];
        } elseif (count($acknowledged) == 0 && count($other) == 0 && count($with_clinic) == 1 && count($without_clinic) >= 1) {
            $decision   = 'delete';
            $reason     = 'No acknowledged row, but exactly one row has a clinic assigned; removing duplicate(s) without a clinic.';
            $delete_ids = array();
            foreach ($without_clinic as $w) {
                $delete_ids[] = $w['id'];
            }
            $keep_id = $with_clinic[0]['id'];
        } elseif (count($acknowledged) == 0 && count($other) == 0) {
            $decision  = 'delete';
            $reason    = 'No acknowledged row and clinic assignment does not distinguish rows; keeping the oldest row (lowest id).';
            $oldest    = $waiting[0];
            foreach ($waiting as $w) {
                if ((int) $w['id'] < (int) $oldest['id']) {
                    $oldest = $w;
                }
            }
            $delete_ids = array();
            foreach ($waiting as $w) {
                if ($w['id'] != $oldest['id']) {
                    $delete_ids[] = $w['id'];
                }
            }
            $keep_id = $oldest['id'];
        } else {
            $decision   = 'skip';
            $delete_ids = array();
            $keep_id    = null;
            if (count($acknowledged) > 1) {
                $reason = 'More than one acknowledged row in this group — needs manual review.';
            } else {
                $reason = 'Group contains a cancelled row — needs manual review.';
            }
        }

        $groups[] = array(
            'outlet_id'  => $outlet_id,
            'v_date'     => $v_date,
            'rows'       => $rows,
            'decision'   => $decision,
            'reason'     => $reason,
            'keep_id'    => $keep_id,
            'delete_ids' => $delete_ids,
        );
    }

    return $groups;
}

function vc_status_label($status) {
    if ($status == '0') return 'Waiting Ack';
    if ($status == '1') return 'Acknowledged';
    if ($status == '2') return 'Cancelled';
    return 'Unknown (' . $status . ')';
}

$outlet_arr = array();
$outlet_res = mysqli_query($conn, "SELECT id, code FROM outlet");
if ($outlet_res) {
    while ($o = $outlet_res->fetch_assoc()) {
        $outlet_arr[$o['id']] = $o['code'];
    }
}

$groups          = array();
$executed        = false;
$deleted_count   = 0;
$exec_error      = '';

if ($action == 'preview' || $action == 'execute') {
    $groups = vc_dedupe_find_groups($conn, $date_from, $date_to);
}

if ($action == 'execute' && isset($_POST['confirm']) && $_POST['confirm'] == '1') {
    $ids_to_delete = array();
    foreach ($groups as $g) {
        if ($g['decision'] == 'delete') {
            foreach ($g['delete_ids'] as $id) {
                $ids_to_delete[] = (int) $id;
            }
        }
    }

    if (!empty($ids_to_delete)) {
        mysqli_begin_transaction($conn);
        $ok = true;
        foreach ($ids_to_delete as $id) {
            if (!mysqli_query($conn, "DELETE FROM vaccine_campaign WHERE id='$id' AND status='0'")) {
                $ok = false;
                break;
            }
        }
        if ($ok) {
            mysqli_commit($conn);
            $deleted_count = count($ids_to_delete);
        } else {
            mysqli_rollback($conn);
            $exec_error = 'A delete failed — no changes were committed. Please check the database logs.';
        }
    }
    $executed = true;
    // Recompute so the page reflects the post-delete state.
    $groups = vc_dedupe_find_groups($conn, $date_from, $date_to);
}

?>
<style type="text/css">
.idx-panel{background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:18px 22px;margin:10px 0;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;text-align:left !important;}
.idx-panel p, .idx-panel div, .idx-panel li { font-size:13px !important; font-family:Arial,Helvetica,sans-serif !important; }
.dd-table{width:100% !important;border-collapse:collapse !important;font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;margin:10px 0 !important;}
.dd-table th,.dd-table td{padding:6px 10px !important;border:1px solid #e2e8f0 !important;text-align:left !important;vertical-align:top !important;}
.dd-table th{background:#f8fafc !important;font-weight:bold !important;}
.dd-delete{color:#c53030 !important;}
.dd-keep{color:#059669 !important;}
.dd-skip{color:#d97706 !important;}
.save-notice{background:#fffbeb !important;border-left:3px solid #d97706 !important;border-radius:6px !important;padding:8px 14px !important;margin:8px 0 !important;color:#92400e !important;}
.save-count{background:#f0fdf4 !important;border-left:3px solid #059669 !important;border-radius:6px !important;padding:8px 14px !important;margin:8px 0 !important;color:#065f46 !important;font-weight:bold !important;}
.save-error{background:#fff5f5 !important;border-left:3px solid #e53e3e !important;border-radius:6px !important;padding:8px 14px !important;margin:8px 0 !important;color:#c53030 !important;font-weight:bold !important;}
select, input[type="checkbox"]{font-size:13px !important;font-family:Arial,Helvetica,sans-serif !important;}
select{padding:5px 8px !important;border-radius:6px !important;border:1px solid #cfcfcf !important;}
button.upd-submit, a.upd-submit, .upd-submit{display:inline-flex !important;align-items:center !important;background:#005B96 !important;color:#fff !important;border:1px solid #005B96 !important;border-radius:8px !important;height:32px !important;padding:5px 18px !important;font-weight:bold !important;cursor:pointer !important;font-family:Arial,Helvetica,sans-serif !important;font-size:13px !important;box-sizing:border-box !important;text-decoration:none !important;line-height:1 !important;}
button.upd-submit:hover, a.upd-submit:hover{background:#004d80 !important;border-color:#004d80 !important;}
button.upd-danger{display:inline-flex !important;align-items:center !important;background:#c53030 !important;color:#fff !important;border:1px solid #c53030 !important;border-radius:8px !important;height:32px !important;padding:5px 18px !important;font-weight:bold !important;cursor:pointer !important;font-family:Arial,Helvetica,sans-serif !important;font-size:13px !important;box-sizing:border-box !important;line-height:1 !important;}
button.upd-danger:hover{background:#9b2c2c !important;}
a.upd-back, .upd-back{display:inline-flex !important;align-items:center !important;background:#e9ecef !important;color:#111 !important;border:1px solid #d0d7de !important;border-radius:8px !important;height:32px !important;padding:5px 18px !important;font-weight:bold !important;text-decoration:none !important;font-family:Arial,Helvetica,sans-serif !important;font-size:13px !important;box-sizing:border-box !important;line-height:1 !important;}
a.upd-back:hover{background:#d8dde3 !important;border-color:#b0b8c1 !important;}
</style>
<div class="idx-panel">
    <h3 style="margin-top:0 !important;">Remove Duplicate Campaigns (August &ndash; December 2026)</h3>
    <p>A duplicate is two or more campaign rows with the <b>same outlet</b> and the <b>same exact date</b>. This tool keeps one row and deletes the rest when:</p>
    <ul>
        <li>Exactly one row is <b>Acknowledged</b> and the other(s) are still <b>Waiting Ack</b> &mdash; keeps the acknowledged row.</li>
        <li>No row is acknowledged, but exactly one row has a <b>clinic assigned</b> and the other(s) don't &mdash; keeps the row with a clinic.</li>
        <li>No row is acknowledged and clinic assignment doesn't distinguish them &mdash; keeps the <b>oldest row (lowest id)</b>.</li>
    </ul>
    <p>Groups that don't match any pattern (multiple acknowledged rows, or a cancelled row present) are left untouched and flagged for manual review.</p>

    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="margin-bottom:10px;">
        <input type="hidden" name="action" value="preview" />
        <button type="submit" class="upd-submit">Preview</button>
        <a href="vaccine_calendar.php" class="upd-back">Back to Calendar</a>
    </form>

    <?php if ($executed) { ?>
        <?php if ($exec_error != '') { ?>
        <div class="save-error"><?php echo htmlspecialchars($exec_error); ?></div>
        <?php } else { ?>
        <div class="save-count"><?php echo $deleted_count; ?> duplicate campaign row(s) deleted for Aug&ndash;Dec 2026.</div>
        <?php } ?>
    <?php } ?>

    <?php if ($action == 'preview' || $action == 'execute') { ?>
        <p>Range checked: <b><?php echo $date_from; ?></b> to <b><?php echo $date_to; ?></b></p>

        <?php if (empty($groups)) { ?>
        <div class="save-notice">No duplicate outlet+date groups found in this range.</div>
        <?php } else { ?>
        <table class="dd-table">
            <tr>
                <th>Outlet</th>
                <th>Date</th>
                <th>Campaign Rows (id : status : clinic)</th>
                <th>Action</th>
                <th>Reason</th>
            </tr>
            <?php foreach ($groups as $g) {
                $code = isset($outlet_arr[$g['outlet_id']]) ? $outlet_arr[$g['outlet_id']] : $g['outlet_id'];
                $rows_txt = array();
                foreach ($g['rows'] as $r) {
                    $clinic_txt = (!empty($r['clinic']) && $r['clinic'] != '0') ? $r['clinic'] : 'none';
                    $rows_txt[] = $r['id'] . ' : ' . vc_status_label($r['status']) . ' : clinic ' . $clinic_txt;
                }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($code); ?></td>
                <td><?php echo htmlspecialchars($g['v_date']); ?></td>
                <td><?php echo htmlspecialchars(implode(', ', $rows_txt)); ?></td>
                <td>
                    <?php if ($g['decision'] == 'delete') { ?>
                        <span class="dd-keep">Keep #<?php echo $g['keep_id']; ?></span> /
                        <span class="dd-delete">Delete #<?php echo implode(', #', $g['delete_ids']); ?></span>
                    <?php } else { ?>
                        <span class="dd-skip">Skip</span>
                    <?php } ?>
                </td>
                <td><?php echo htmlspecialchars($g['reason']); ?></td>
            </tr>
            <?php } ?>
        </table>

        <?php
        $delete_total = 0;
        foreach ($groups as $g) {
            if ($g['decision'] == 'delete') {
                $delete_total += count($g['delete_ids']);
            }
        }
        ?>

        <?php if ($delete_total > 0 && !$executed) { ?>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return confirm('Delete <?php echo $delete_total; ?> duplicate campaign row(s)? This cannot be undone.');">
            <input type="hidden" name="action" value="execute" />
            <div class="save-notice">This will permanently delete <?php echo $delete_total; ?> row(s) marked "Delete" above.</div>
            <label><input type="checkbox" name="confirm" value="1" required /> I have reviewed the rows above and confirm this deletion.</label>
            <div style="margin-top:10px;">
                <button type="submit" class="upd-danger">Confirm &amp; Delete Duplicates</button>
            </div>
        </form>
        <?php } elseif ($delete_total == 0 && !empty($groups)) { ?>
        <div class="save-notice">No rows are eligible for automatic deletion. All groups above need manual review.</div>
        <?php } ?>

        <?php } ?>
    <?php } ?>
</div>
<?php
$connect = 0;
include('../common/index_adv.php');
