<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of report functions.
 *
 * @package   blocks-censusreport
 * @author    Justin Filip <jfilip@remote-learner.net>
 * @copyright 2009 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function bcr_lowercase(&$item, $key) {
    $item = strtolower($item);
}

/**
 * Displays the query setup screen.
 *
 * @param int $cid         The course ID.
 * @param int $iid         The instance ID.
 * @param object $formdata The form data
 * @param string $message  A message
 * @return none
 * @uses $CFG
 */
function bcr_setup_site_query($cid, $iid, $formdata = false, $message='') {
    global $CFG;

    $PAGE = page_create_object(PAGE_COURSE_VIEW, SITEID);
    $PAGE->print_header(get_string('setupquery', 'block_censusreport'),
                        array(get_string('reportlink', 'block_censusreport') => ''));

    // Display form:
    $courses = get_courses("all", "c.sortorder ASC", "c.id,c.shortname");
    if (empty($courses)) {
        error(get_string('nocourses', 'block_censusreport'), $CFG->wwwroot);
    }
    $coursemenu = array();
    foreach ($courses as $course) {
        if ($course->id == SITEID) {
            continue;
        }
        $coursemenu[$course->id] = $course->shortname;
    }

    print_simple_box_start('center', '80%');

    echo '<form action="report.php" method="post">'."\n";

    echo '<fieldset>'."\n";
    echo '<input type="hidden" name="instanceid" value="'. $iid .'" />' ."\n";
    echo '<legend>'.get_string('querytitle', 'block_censusreport').'</legend>'."\n";

    echo '<table cellpadding="4" cellspacing="0" border="0">';
    echo '<tbody>';

    echo '<tr>';
    echo '<td>';
    choose_from_menu($coursemenu, 'course', (!empty($formdata->course)?$formdata->course:''), '');
    echo '</td>';

    echo '<td>from: ';
    print_date_selector('sinceday', 'sincemonth', 'sinceyear', (isset($formdata->startdate)?$formdata->startdate:0));
    echo ' to: ';
    print_date_selector('today', 'tomonth', 'toyear', (isset($formdata->enddate)?$formdata->enddate:0));
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2" align="center">';
    echo '<input type="hidden" name="id" value="'.SITEID.'" /> ';
    echo '<input type="hidden" name="group" value="0" /> ';
    echo '<input type="submit" name="action" value="'.get_string('getreport','block_censusreport').'" /> ';
    echo '<input type="submit" name="action" value="'.get_string('downloadreportpdf','block_censusreport').'" /> ';
    echo '<input type="submit" name="action" value="'.get_string('downloadreportcsv','block_censusreport').'" /> ';
    echo '<input type="submit" name="action" value="'.get_string('exit','block_censusreport').'" /></td>';
    echo '</td>';
    echo '</tr>';

    echo '</tbody></table>';

    if (!empty($message)) {
        notify($message, 'notifysuccess');
    }

    echo '</fieldset>'."\n";
    echo '</form>'."\n";

    print_simple_box_end();

    print_footer();
}


/**
 * Displays the query setup screen.
 *
 * @param int $cid         The course id.
 * @param int $iid         The instance id.
 * @param object $formdata The form data
 * @param string $message  A message
 * @return none
 */
function bcr_setup_course_query($cid, $iid, $formdata=false, $message='') {
    $course = get_record('course', 'id', $cid);

    if (!$course) {
        error ('This course doesn\'t exist!');
    }

    $PAGE = page_create_object(PAGE_COURSE_VIEW, $cid);
    $PAGE->print_header(get_string('setupquery', 'block_censusreport'),
                        array(get_string('reportlink', 'block_censusreport') => ''));

    print_simple_box_start('center', '80%');

    echo '<form action="report.php" method="post">'."\n";

    echo '<fieldset>'."\n";
    echo '<legend>'.get_string('querytitle', 'block_censusreport').'</legend>'."\n";

    echo '<table cellpadding="4" cellspacing="0" border="0">';
    echo '<tbody>';

    echo '<tr>';
    echo '<td>';
    echo '<input type="hidden" name="course" value="'.$cid.'" /> ';
    echo '<input type="hidden" name="instanceid" value="'.$iid.'" /> ';
    p($course->fullname);
    echo '</td>';

    echo '<td>from: ';
    print_date_selector('sinceday', 'sincemonth', 'sinceyear', (isset($formdata->startdate)?$formdata->startdate:0));
    echo ' to: ';
    print_date_selector('today', 'tomonth', 'toyear', (isset($formdata->enddate)?$formdata->enddate:0));
    echo '</td>';
    echo '</tr>';

    if (($grouprecs = groups_get_all_groups($course->id, 0, 0, 'g.id,g.name'))) {
        $groups = array();
        foreach ($grouprecs as $grouprec) {
            $groups[$grouprec->id] = $grouprec->name;
        }
        echo '<tr>';
        echo '<td>';
        echo get_string('groupselector', 'block_censusreport').': ';
        echo '</td>';

        echo '<td>';
        $groups = array('0' => 'All groups') + $groups;
        choose_from_menu ($groups, 'group', 0);
        echo '</td>';
        echo '</tr>';
    }

    echo '<tr>';
    echo '<td colspan="2" align="center">';
    echo '<input type="hidden" name="id" value="'.$cid.'" /> ';
    echo '<input type="submit" name="action" value="'.get_string('getreport','block_censusreport').'" /> ';
    echo '<input type="submit" name="action" value="'.get_string('downloadreportpdf','block_censusreport').'" /> ';
    echo '<input type="submit" name="action" value="'.get_string('downloadreportcsv','block_censusreport').'" /> ';
    echo '<input type="submit" name="action" value="'.get_string('exit','block_censusreport').'" /></td>';
    echo '</td>';
    echo '</tr>';

    echo '</tbody></table>';

    if (!empty($message)) {
        notify($message, 'notifysuccess');
    }

    echo '</fieldset>'."\n";
    echo '</form>'."\n";

    print_simple_box_end();

    print_footer();
}


/**
 * Performs the report function.
 *
 * Note: Jan 20/10 - making change to add course group level support.  site level support
 * still needs to be put in.
 *
 * @param object $block    The block instance
 * @param object $formdata The form data
 * @param string $type     The report type
 * @return bool Success/failure
 * @uses $CFG
 */
function bcr_generate_report($block, $formdata, $type='view') {
    global $CFG;

    $blockname     = 'block_censusreport';
    $group         = isset($formdata->group)  ? $formdata->group : 0;
    $course  = get_record('course', 'id', $formdata->course);
    $groupusers    = array();

    if (!empty($group)) {
        // Get specific group members only
        $groupusers = groups_get_members($group, 'u.id');

    } else {
        // Get all userss within the course
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        $groupusers   = get_users_by_capability($context, 'moodle/legacy:student',
                       'u.id', 'u.lastname ASC, u.firstname ASC', '', '', '', '', false, true);
    }

    if (!empty($groupusers)) {
        $groupusers = array_keys($groupusers);
    }

    $results = bcr_build_grades_array($formdata->course, $groupusers, $formdata->startdate, $formdata->enddate, $group);

    if (!$results) {
        return false;
    }

    $showstudentid = $block->check_field_status('showstudentid', $type);
    $headers = array('student' => get_string('student', $blockname));
    if ($showstudentid) {
        $headers['studentid'] = get_string('studentid', $blockname);
    }
    $headers['activity'] = get_string('activity', $blockname);
    $headers['grade'] = get_string('grade', $blockname);
    $headers['date'] = get_string('date', $blockname);

    $numusers = count($results);
    $numcols  = count($headers);

    if (!$numusers || !$course) {
        echo 'Nothing to report';
        return false;
    }

    $showteachername = $block->check_field_status('showteachername', $type);
    $teachers = array();

    $align = array('left');
    if ($showstudentid) {
        $align[] = 'left';
    }

    for ($i = 0; $i < $numcols; $i++) {
        $align[] = 'center';
    }
    $align[] = 'center';

    require_once('report.class.php');
    $tenp_report = new report($course->id);
    $tenp_report->type        = 'classroster';
    $tenp_report->fileformats = array(
        'pdf'   => get_string('pdf', $blockname),
        'csv'   => get_string('csv', $blockname),
        'excel' => get_string('excel', $blockname)
    );

    $context = get_context_instance(CONTEXT_COURSE, $course->id);

    if ($managerroles = get_config('', 'coursemanager')) {
        $coursemanagerroles = split(',', $managerroles);

        if ($roles = get_records_select( 'role', '', 'sortorder' )) {
            foreach ($roles as $role) {
                if (in_array( $role->id, $coursemanagerroles )) {
                    if ($users = get_role_users($role->id, $context, true, '', 'u.lastname ASC, u.firstname ASC', true)) {
                        $namesarray = array();

                        foreach ($users as $teacher) {
                            $fullname = fullname($teacher, has_capability('moodle/site:viewfullnames', $context));

                            if (!in_array($fullname, $namesarray)) {
                                $namesarray[] = $fullname;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($namesarray)) {
            $instructors = implode(', ', $namesarray);
        } else {
            $instructors = ' - ';
        }
    }

    $table = new stdClass;
    $table->head = $headers;
    $table->align = $align;
    $table->data = array();
    $tenp_report->headers = $headers;
    $tenp_report->data = array();

    // Get student legacy role
    $legacyroles = get_legacy_roles();
    $studentcapability = $legacyroles['student'];

    foreach ($results as $result) {
        if (has_capability($studentcapability, $context, $result->userid, false)) {
            if ($showstudentid) {
                $datum = array($result->student, $result->studentid, $result->activity, $result->grade, $result->date);
            } else {
                $datum = array($result->student, $result->activity, $result->grade, $result->date);
            }
            $table->data[] = $datum;
            $pdatm = new Object();
            $pdatm->student = $result->student;
            $pdatm->studentid = $result->studentid;
            $pdatm->activity = $result->activity;
            $pdatm->grade = $result->grade;
            $pdatm->date = $result->date;
            $tenp_report->data[] = $pdatm;
        }
    }

    if ($type == 'view') {
        $PAGE = page_create_object(PAGE_COURSE_VIEW, $formdata->id);
        $PAGE->print_header(get_string('setupquery', $blockname),
                            array(get_string('reportlink', $blockname) => ''));


        if ($block->check_field_status('showcoursename', 'view')) {
            echo '<b>'. get_string('coursetitle', $blockname) .':</b> '. $course->fullname .'<br />';
        }
        if ($block->check_field_status('showcoursecode', 'view')) {
            echo '<b>'. get_string('coursecode', $blockname) .':</b> '. $course->shortname .'<br />';
        }
        if ($block->check_field_status('showcourseid', 'view') && $course->idnumber !== '') {
            echo '<b>'. get_string('courseid', $blockname) .':</b> '. $course->idnumber .'<br />';
        }

        if (!empty($formdata->group)) {
            echo '<b>' . get_string('section', $blockname) . ':</b> ' .
                 groups_get_group_name($formdata->group) . '<br />';
        }

        if ($block->check_field_status('showteachername', 'view')) {
            echo '<b>' . get_string('instructor', $blockname) . ':</b> ' . $instructors . '<br /><br />';
        }

        print_table($table);

        echo '<br /><br /><div align="center">'
           . '<a href="'.$CFG->wwwroot.'/blocks/censusreport/report.php?id='.$formdata->id
           . ((isset($block->instance)) ? '&instanceid='. $block->instance->id : '') .'">Back to reports</a></div>';

        print_footer();

    } else if ($type == 'pdf') {
        $tenp_report->filename = 'censusreport';
        $tenp_report->title = get_string('censusreport_title', $blockname);

        if ($block->check_field_status('showcoursename', 'pdf')) {
            $tenp_report->top = get_string('coursetitle', $blockname) .': '. $course->fullname . "\n";
        }

        if ($block->check_field_status('showcoursecode', 'pdf')) {
            $tenp_report->top .= get_string('coursecode', $blockname) .': '. $course->shortname . "\n";
        }

        if ($block->check_field_status('showcourseid', 'pdf') && $course->idnumber !== '') {
            $tenp_report->top .= get_string('courseid', $blockname) .': '. $course->idnumber . "\n";
        }

        if (!empty($formdata->group)) {
            $tenp_report->top .= get_string('section', $blockname) . ': ' .
                                   groups_get_group_name($formdata->group) . "\n";
        }

        if ($block->check_field_status('showteachername', 'pdf')) {
            $tenp_report->top .= get_string('instructor', $blockname) . ': ' . $instructors;
        }

        if ($block->check_field_status('showsignatureline', 'pdf')) {
            $tenp_report->signatureline = true;
        }

        if ($block->check_field_status('showdateline', 'pdf')) {
            $tenp_report->dateline = true;
        }

        if ($block->check_field_status('footermessage', 'pdf')) {
            $tenp_report->bottom .= "\n". $block->config->footermessagepdf ."\n";
        }
        $tenp_report->titlealign = 'L';
        $tenp_report->fontsize = 14;
        $tenp_report->download('pdf');

    } else if ($type == 'csv') {
        $filename = 'censusreport.csv';

        if (!empty($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
            header('Expires: 0');
            header('Cache-Control: private, pre-check=0, post-check=0, max-age=0, must-revalidate');
            header('Connection: Keep-Alive');
            header('Content-Language: ' . current_language());
            header('Keep-Alive: timeout=5, max=100');
            header('Pragma: no-cache');
            header('Pragma: expires');
            header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Content-Transfer-Encoding: ascii');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Content-Type: text/comma-separated-values');
        } else {
            header('Content-Transfer-Encoding: ascii');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Content-Type: text/comma-separated-values');
        }

        echo get_string('censusreport_title', 'block_censusreport') . "\n";
        $fields = array(0 => array(), 1 => array());

        if ($block->check_field_status('showcoursename', 'csv')) {
            $fields[0][] = get_string('coursetitle', $blockname);
            $fields[1][] = $course->fullname;
        }

        if ($block->check_field_status('showcoursecode', 'csv')) {
            $fields[0][] = get_string('coursecode', $blockname);
            $fields[1][] = $course->shortname;
        }

        if ($block->check_field_status('showcourseid', 'csv') && ($course->idnumber !== '')) {
            $fields[0][] = get_string('courseid', $blockname);
            $fields[1][] = $course->idnumber;
        }
        if ($block->check_field_status('showteachername', 'csv')) {
            $fields[0][] = get_string('instructor', $blockname);
            $fields[1][] = str_replace(',', ';', $instructors);
        }
        if (sizeof($fields[0]) > 0) {
            echo implode(',', $fields[0]) ."\n";
            echo implode(',', $fields[1]) ."\n";
        }

        echo "\n". implode(',', $table->head) . "\n";
        foreach ($table->data as $row) {
            $trow = array();
            foreach ($row as $coldata) {
                $trow[] = bcr_csv_escape_string(strip_tags($coldata));
            }
            echo implode(',', $trow) . "\n";
        }

        $showsignatureline = $block->check_field_status('showsignatureline', 'csv');
        $showdateline      = $block->check_field_status('showdateline', 'csv');

        if ($showsignatureline && $showdateline) {
            echo "\n";
        }

        if ($showsignatureline) {
            echo get_string('certified', 'block_censusreport') ."\n";
            echo get_string('signature', 'block_censusreport') .":\n";
        }

        if ($showdateline) {
            echo get_string('date') .":\n";
        }
    }
}


/**
 * Build the array of grades for the report.
 *
 * @param int   $courseid    The course record ID.
 * @param mixed $useridorids A user ID or an array of user IDs (optional).
 * @param int   $startdate   The start date for the time period to fetch logs (optional).
 * @param int   $enddate     The end date for the time period to fetch logs (optional).
 * @param int   $groupid     Limit the results to a specific group ID (optional).
 * @return array An array of user course log information.
 */
function bcr_build_grades_array($courseid, $useridorids = 0, $startdate = 0, $enddate = 0, $groupid = 0) {
    global $CFG;

    require_once($CFG->dirroot.'/lib/gradelib.php');
    require_once($CFG->dirroot.'/lib/grade/constants.php');
    require_once($CFG->dirroot.'/lib/grade/grade_item.php');

    $role           = get_default_course_role($courseid);
    $context        = get_context_instance(CONTEXT_COURSE, $courseid);
    $results        = array();
    $gis            = array();

    // Pass #1 - Search through grade_grades_history for grade data
    $sql = "SELECT ggh.id, gi.id as giid, u.id as userid, u.firstname, u.lastname, u.idnumber, gi.itemname,
                   ggh.finalgrade, ggh.timemodified AS timecreated
            FROM {$CFG->prefix}grade_items gi
            INNER JOIN {$CFG->prefix}role_assignments ra
            INNER JOIN {$CFG->prefix}grade_grades_history ggh ON (ggh.itemid = gi.id AND ggh.userid = ra.userid)
            INNER JOIN {$CFG->prefix}user u ON u.id = ra.userid " .
            ($groupid != 0 ? "INNER JOIN {$CFG->prefix}groups_members gm ON gm.userid = u.id " : '') . "
            WHERE gi.courseid = {$courseid}
            AND gi.itemtype = 'mod'
            AND ra.roleid = {$role->id}
            AND ra.contextid = {$context->id}
            AND ggh.timemodified >= {$startdate}
            AND ggh.timemodified <= {$enddate} " .
            ($groupid != 0 ? "AND gm.groupid = {$groupid} " : '') . "
            GROUP BY userid
            ORDER BY ggh.timemodified ASC, u.lastname ASC, u.firstname ASC";

    if ($rs = get_recordset_sql($sql)) {
        while ($record = rs_fetch_next_record($rs)) {
            if (empty($gis[$record->giid])) {
                $gis[$record->giid] = new grade_item(array('id' => $record->giid));
            }

            $result = new stdClass;
            $result->userid      = $record->userid;
            $result->lastname    = $record->lastname;
            $result->firstname   = $record->firstname;
            $result->student     = fullname($record);
            $result->studentid   = $record->idnumber;
            $result->activity    = $record->itemname;
            $result->grade       = grade_format_gradevalue($record->finalgrade, &$gis[$record->giid]);
            $result->timecreated = $record->timecreated;
            $result->date        = strftime('%m/%d/%y', $record->timecreated);
            $results[$record->userid] = $result;
        }
        rs_close($rs);
    }


    // Pass #2 - Get general grade item records from the DB and compare dates with grade_grades_history entries
    $sql = "SELECT gg.id, gi.id as giid, u.id as userid, u.firstname, u.lastname, u.idnumber, gi.itemname,
                   gg.finalgrade, gg.timecreated, gg.timemodified
            FROM {$CFG->prefix}grade_items gi
            INNER JOIN {$CFG->prefix}role_assignments ra
            INNER JOIN {$CFG->prefix}grade_grades gg ON (gg.itemid = gi.id AND gg.userid = ra.userid)
            INNER JOIN {$CFG->prefix}user u ON u.id = ra.userid " .
            ($groupid != 0 ? "INNER JOIN {$CFG->prefix}groups_members gm ON gm.userid = u.id " : '') . "
            WHERE gi.courseid = {$courseid}
            AND gi.itemtype = 'mod'
            AND ra.roleid = {$role->id}
            AND ra.contextid = {$context->id}
            AND ( ( gg.timecreated >= {$startdate} AND gg.timecreated <= {$enddate} )
            OR  ( gg.timemodified >= {$startdate} AND gg.timemodified <= {$enddate} ) )" .
            ($groupid != 0 ? "AND gm.groupid = {$groupid} " : '') . "
            GROUP BY gg.userid
            ORDER BY MIN(gg.timecreated) ASC, u.lastname ASC, u.firstname ASC";

    if ($rs = get_recordset_sql($sql)) {
        while ($record = rs_fetch_next_record($rs)) {
            if (empty($gis[$record->giid])) {
                $gis[$record->giid] = new grade_item(array('id' => $record->giid));
            }

            // Some graded items only use the timemodified field and a null value for the timecreated field
            $time = empty($record->timecreated) ? $record->timemodified : $record->timecreated;

            if (empty($results[$record->userid])) {

                $result = new stdClass;
                $result->userid      = $record->userid;
                $result->lastname    = $record->lastname;
                $result->firstname   = $record->firstname;
                $result->student     = fullname($record);
                $result->studentid   = $record->idnumber;
                $result->activity    = $record->itemname;
                $result->grade       = grade_format_gradevalue($record->finalgrade, &$gis[$record->giid]);
                $result->timecreated = $time;
                $result->date        = strftime('%m/%d/%y', $record->timecreated);
                $results[$record->userid] = $result;
            } else if ($time < $results[$record->userid]->timecreated) {

                $results[$record->userid]->activity = $record->itemname;
                $results[$record->userid]->grade = grade_format_gradevalue($record->finalgrade, &$gis[$record->giid]);
                $results[$record->userid]->timecreated = $time;

            }
        }
        rs_close($rs);
    }

    // Pass #3 - Get any graded forum post records from the DB
    $sql = "SELECT gg.id, fp.id as postid, gi.id AS giid, u.id as userid, u.firstname, u.lastname, u.idnumber,
                   gi.itemname, gg.finalgrade, fp.created as timecreated
            FROM {$CFG->prefix}forum_posts fp
            INNER JOIN {$CFG->prefix}forum_discussions fd ON fd.id = fp.discussion
            INNER JOIN {$CFG->prefix}grade_items gi ON gi.iteminstance = fd.forum
            INNER JOIN {$CFG->prefix}grade_grades gg ON (gg.itemid = gi.id AND gg.userid = fp.userid)
            INNER JOIN {$CFG->prefix}role_assignments ra ON ra.userid = fp.userid
            INNER JOIN {$CFG->prefix}user u ON u.id = fp.userid " .
            ($groupid != 0 ? "INNER JOIN {$CFG->prefix}groups_members gm ON gm.userid = u.id " : '') . "
            WHERE fd.course = $courseid
            AND fp.userid != 0
            AND gi.itemmodule = 'forum'
            AND ra.contextid = {$context->id}
            AND fp.created >= $startdate
            AND fp.created <= $enddate " .
            ($groupid != 0 ? "AND gm.groupid = {$groupid} " : '') . "
            GROUP BY fp.userid
            ORDER BY fp.created ASC, u.lastname ASC, u.firstname ASC";

    if ($rs = get_recordset_sql($sql)) {
        while ($record = rs_fetch_next_record($rs)) {

            if (empty($gis[$record->giid])) {
                $gis[$record->giid] = new grade_item(array('id' => $record->giid));
            }

            /// Only record the oldest record found.
            if (empty($results[$record->userid]) || ($record->timecreated < $results[$record->userid]->timecreated)) {

                $result = new stdClass;
                $result->userid      = $record->userid;
                $result->lastname    = $record->lastname;
                $result->firstname   = $record->firstname;
                $result->student     = fullname($record);
                $result->studentid   = $record->idnumber;
                $result->activity    = $record->itemname;
                $result->grade       = grade_format_gradevalue($record->finalgrade, &$gis[$record->giid]);
                $result->timecreated = $record->timecreated;
                $result->date        = strftime('%m/%d/%y', $record->timecreated);
                $results[$record->userid] = $result;
            }
        }

        rs_close($rs);
    }

    // Sort the resulting data by using a "lastname ASC, firstname ASC" sorting algorithm
    usort($results, 'bcr_results_sort');

    return $results;
}

/**
 * Makes a string safe for CSV output.
 *
 * Replaces unsafe characters with whitespace and escapes
 * double-quotes within a column value.
 *
 * @param string $input The input string.
 * @return string A CSV export 'safe' string.
 */
function bcr_csv_escape_string($input) {
    $input = ereg_replace("[\r\n\t]", ' ', $input);
    $input = ereg_replace('"', '""', $input);
    $input = '"' . $input . '"';

    return $input;
}

/**
 * Sorts the results fetched from the main data-gathering function.
 *
 * The results must contain a 'firstname' and 'lastname' property for proper sorting.
 *
 * @param array $results A reference to the array of results.
 */
function bcr_results_sort($a, $b) {
    $a1 = strtolower($a->lastname);
    $b1 = strtolower($b->lastname);
    $a2 = strtolower($a->firstname);
    $b2 = strtolower($b->lastname);

    // Compare the lastname values
    $comp = strcmp($a1, $b1);

    if ($comp == 0) {
        // If they are equal, return the comparison between the firstname values
        return strcmp($a2, $b2);
    } else {
        // Otherwise, return the lastname comparison value as-is
        return $comp;
    }
}

?>
