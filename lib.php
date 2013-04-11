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
 * @author    James McQuillan <james.mcquillan@remote-learner.net>
 * @copyright 2009 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');

/**
 * CENSUS_ACTION_VIEW - represents viewing the HTML output of the report
 */
define('CENSUS_ACTION_VIEW', 1);

/**
 * CENSUS_ACTION_DLPDF - represents download the report output in PDF format
 */
define('CENSUS_ACTION_DLPDF', 2);

/**
 * CENSUS_ACTION_DLCSV - represents download the report output in CSV format
 */
define('CENSUS_ACTION_DLCSV', 3);

/**
 * REPORT_IMAGE_HEADER - represents the header image directory
 */
define('REPORT_IMAGE_HEADER', 'header');

/**
 * REPORT_IMAGE_LOGO - represents the logo image directory
 */
define('REPORT_IMAGE_LOGO', 'logo');

class bcr_setup_query_form extends moodleform {

    protected $iid;

    function __construct($actionurl, $iid, $cid) {
        $this->iid = $iid;
        $this->cid = $cid;
        parent::moodleform($actionurl);
    }

    function definition() {
        global $DB, $CFG;
        $mform =& $this->_form;

        $mform->addElement('header', 'header', get_string('querytitle', 'block_censusreport'));
        $mform->addElement('hidden', 'instanceid', $this->iid);
        $mform->addElement('hidden', 'id', $this->cid);

        if ($this->cid == SITEID) {

            $courses = get_courses('all', 'c.sortorder ASC', 'c.id, c.shortname');
            if (empty($courses)) {
                print_error(get_string('nocourses', 'block_censusreport'), $CFG->wwwroot);
            }
            $coursemenu = array();
            foreach ($courses as $course) {
                if ($course->id == SITEID) {
                    continue;
                }
                $coursemenu[$course->id] = $course->shortname;
            }

            unset($courses);
            $mform->addElement('select', 'course', get_string('course', 'block_censusreport'), $coursemenu);

        } else {

            $course = $DB->get_record('course', array('id' => $this->cid));
            if (empty($course)) {
                print_error('coursedoesnotexist', 'block_census_report');
            }

            $mform->addElement('hidden', 'course', $this->cid);
            $mform->addElement('static', 'course_label', get_string('course', 'block_censusreport'), $course->fullname);

            if (($grouprecs = groups_get_all_groups($course->id, 0, 0, 'g.id, g.name'))) {
                $groups = array();
                foreach ($grouprecs as $grouprec) {
                    $groups[$grouprec->id] = $grouprec->name;
                }
                $groups = array('0' => get_string('allgroups', 'block_censusreport')) + $groups;
                $mform->addElement('select', 'group', get_string('groupselector', 'block_censusreport'), $groups);
            }
        }

        $mform->addElement('date_selector', 'startdate', get_string('from'));
        $mform->addElement('date_selector', 'enddate', get_string('to'));

        $mform->addElement('html', '<br />');

        $bview  =& $mform->createElement('radio', 'action', '', get_string('viewreport', 'block_censusreport'), CENSUS_ACTION_VIEW);
        $bdlpdf =& $mform->createElement('radio', 'action', '', get_string('downloadreportpdf', 'block_censusreport'), CENSUS_ACTION_DLPDF);
        $bdlcsv =& $mform->createElement('radio', 'action', '', get_string('downloadreportcsv', 'block_censusreport'), CENSUS_ACTION_DLCSV);

        $actions = array(
            $bview,
            $bdlpdf,
            $bdlcsv
        );
        $mform->addGroup($actions, 'action', get_string('action','block_censusreport'), array(' '), false);
        $mform->setDefault('action', CENSUS_ACTION_VIEW);

        $mform->addElement('html', '<br />');

        $bsubmit =& $mform->createElement('submit', 'submitbutton', get_string('getreport', 'block_censusreport'));
        $breset  =& $mform->createElement('reset', 'resetbutton', get_string('revert'));
        $bcancel =& $mform->createElement('cancel');

        $submits = array(
            $bsubmit,
            $breset,
            $bcancel
        );
        $mform->addGroup($submits, 'submits', '&nbsp;', array(' '), false);
    }
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
function bcr_generate_report($block, $formdata, $type = 'view') {
    global $CFG, $DB;

    $blockname  = 'block_censusreport';
    $group      = isset($formdata->group) ? $formdata->group : 0;
    $course     = $DB->get_record('course', array('id' => $formdata->course));
    $groupusers = array();

    if (!empty($group)) {
        // Get specific group members only
        $groupusers = groups_get_members($group, 'u.id');

    } else {
        // Get all userss within the course
        $context    = context_course::instance($course->id);
        $groupusers = get_users_by_capability($context, 'moodle/course:isincompletionreports', 'u.id', 'u.lastname ASC, u.firstname ASC',
                                               '', '', '', '', false, true);
    }

    if (!empty($groupusers)) {
        $groupusers = array_keys($groupusers);
    }

    $showallstudents = $block->check_field_status('showallstudents');
    $results = bcr_build_grades_array($formdata->course, $groupusers, $formdata->startdate, $formdata->enddate, $group, $showallstudents);

    if (empty($results)) {
        return false;
    }

    $showstudentid = $block->check_field_status('showstudentid', $type);
    $headers = array('student' => get_string('student', $blockname));
    if ($showstudentid) {
        $headers['studentid'] = get_string('studentid', $blockname);
    }
    $headers['activity'] = get_string('activity', $blockname);
    $headers['grade']    = get_string('grade', $blockname);
    $headers['date']     = get_string('date', $blockname);

    $numusers = count($results);
    $numcols  = count($headers);

    if (!$numusers || !$course) {
        //get_string('nothingtoreport','block_censusreport');
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
    $tenpreport = new report($course->id);
    $tenpreport->type        = 'classroster';
    $tenpreport->fileformats = array(
        'pdf'   => get_string('pdf', $blockname),
        'csv'   => get_string('csv', $blockname),
        'excel' => get_string('excel', $blockname)
    );

    $context = context_course::instance($course->id);
    $namesarrayview = array();
    $namesarraypdf = array();
    $instructors = ' - ';
    if (!empty($CFG->coursecontact)) {
        $coursecontactroles = explode(',', $CFG->coursecontact);
        foreach ($coursecontactroles as $roleid) {
            $role   = $DB->get_record('role', array('id' => $roleid));
            $roleid = (int)$roleid;
            if ($users = get_role_users($roleid, $context, true)) {
                foreach ($users as $teacher) {
                    $fullname = fullname($teacher, has_capability('moodle/site:viewfullnames', $context));
                    $namesarrayview[] = format_string(role_get_name($role, $context)).': <a href="'.$CFG->wwwroot.'/user/view.php?id='.
                                                      $teacher->id.'&amp;course='.SITEID.'">'.$fullname.'</a>';
                    $namesarraypdf[]  = format_string(role_get_name($role, $context)).': '.$fullname;
                }
            }
        }
    }

    $table = new html_table();
    $table->head = $headers;
    $table->align = $align;
    $table->data = array();
    $tenpreport->headers = $headers;
    $tenpreport->data = array();

    foreach ($results as $result) {
        $addusertoreport = false;
        $roles = get_user_roles($context, $result->userid, false);

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if (!empty($role->shortname) && $role->shortname === 'student') {
                    $addusertoreport = true;
                }
            }
        }

        if ($addusertoreport === true) {
            if ($showstudentid) {
                $datum = array($result->student, $result->studentid, $result->activity, $result->grade, $result->date);
            } else {
                $datum = array($result->student, $result->activity, $result->grade, $result->date);
            }
            $table->data[] = $datum;

            $pdatm = new Object();
            $pdatm->student   = $result->student;
            $pdatm->studentid = $result->studentid;
            $pdatm->activity  = $result->activity;
            $pdatm->grade     = $result->grade;
            $pdatm->date      = $result->date;
            $tenpreport->data[] = $pdatm;
        }
    }

    if ($type == 'view') {
        if ($block->check_field_status('showcoursename', 'view')) {
            echo '<b>'.get_string('coursetitle', $blockname).':</b> '.$course->fullname.'<br />';
        }
        if ($block->check_field_status('showcoursecode', 'view')) {
            echo '<b>'.get_string('coursecode', $blockname).':</b> '.$course->shortname.'<br />';
        }
        if ($block->check_field_status('showcourseid', 'view') && $course->idnumber !== '') {
            echo '<b>'.get_string('courseid', $blockname).':</b> '.$course->idnumber.'<br />';
        }

        if (!empty($formdata->group)) {
            echo '<b>'.get_string('section', $blockname).':</b> '.groups_get_group_name($formdata->group).'<br />';
        }

        if ($block->check_field_status('showteachername', 'view')) {
            if (!empty($namesarrayview)) {
                $instructors = implode(', ', $namesarrayview);
                echo '<b>'.get_string('instructor', $blockname).':</b> '.$instructors.'<br /><br />';
            }
        }

        echo html_writer::table($table);

        echo '<br /><br /><div align="center"><a href="'.$CFG->wwwroot.'/blocks/censusreport/report.php?id='.$formdata->id. 
             ((isset($block->instance) && !empty($block->instance)) ? '&instanceid='.$block->instance->id : '').'">'.
             get_string('backtoreports', 'block_censusreport').'</a></div>';

    } else if ($type == 'pdf') {
        $tenpreport->filename = 'censusreport';
        $tenpreport->title    = get_string('censusreport_title', $blockname);

        if ($block->check_field_status('showcoursename', 'pdf')) {
            $tenpreport->top = get_string('coursetitle', $blockname).': '.$course->fullname."\n";
        }

        if ($block->check_field_status('showcoursecode', 'pdf')) {
            $tenpreport->top .= get_string('coursecode', $blockname).': '.$course->shortname."\n";
        }

        if ($block->check_field_status('showcourseid', 'pdf') && $course->idnumber !== '') {
            $tenpreport->top .= get_string('courseid', $blockname).': '.$course->idnumber."\n";
        }

        if (!empty($formdata->group)) {
            $tenpreport->top .= get_string('section', $blockname).': '.groups_get_group_name($formdata->group)."\n";
        }

        if ($block->check_field_status('showteachername', 'pdf')) {
            if (!empty($namesarrayview)) {
                $instructors = implode(', ', $namesarrayview);
            }
            $tenpreport->top .= get_string('instructor', $blockname).': '.strip_tags($instructors);
        }

        if ($block->check_field_status('showsignatureline', 'pdf')) {
            $tenpreport->signatureline = true;
        }

        if ($block->check_field_status('showdateline', 'pdf')) {
            $tenpreport->dateline = true;
        }

        if ($block->check_field_status('footermessage', 'pdf')) {
            $tenpreport->bottom .= "\n".$block->config->footermessagepdf."\n";
        }
        $tenpreport->titlealign = 'L';
        $tenpreport->fontsize   = 14;
        $tenpreport->download('pdf');

    } else if ($type == 'csv') {
        $filename = 'censusreport.csv';

        if (!empty($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
            header('Expires: 0');
            header('Cache-Control: private, pre-check=0, post-check=0, max-age=0, must-revalidate');
            header('Connection: Keep-Alive');
            header('Content-Language: '.current_language());
            header('Keep-Alive: timeout=5, max=100');
            header('Pragma: no-cache');
            header('Pragma: expires');
            header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
            header('Content-Transfer-Encoding: ascii');
            header('Content-Disposition: attachment; filename='.$filename);
            header('Content-Type: text/comma-separated-values');
        } else {
            header('Content-Transfer-Encoding: ascii');
            header('Content-Disposition: attachment; filename='.$filename);
            header('Content-Type: text/comma-separated-values');
        }

        echo get_string('censusreport_title', 'block_censusreport')."\n";
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
            if (!empty($namesarrayview)) {
                $instructors = implode(', ', $namesarrayview);
            }
            $fields[0][] = get_string('instructor', $blockname);
            $fields[1][] = str_replace(',', ';', $instructors);
        }
        if (sizeof($fields[0]) > 0) {
            echo implode(',', $fields[0])."\n";
            echo implode(',', $fields[1])."\n";
        }

        echo "\n". implode(',', $table->head)."\n";
        foreach ($table->data as $row) {
            $trow = array();
            foreach ($row as $coldata) {
                $trow[] = bcr_csv_escape_string(strip_tags($coldata));
            }
            echo implode(',', $trow)."\n";
        }

        $showsignatureline = $block->check_field_status('showsignatureline', 'csv');
        $showdateline      = $block->check_field_status('showdateline', 'csv');

        if ($showsignatureline && $showdateline) {
            echo "\n";
        }

        if ($showsignatureline) {
            echo get_string('certified', 'block_censusreport')."\n";
            echo get_string('signature', 'block_censusreport').":\n";
        }

        if ($showdateline) {
            echo get_string('date').":\n";
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
function bcr_build_grades_array($courseid, $useridorids = 0, $startdate = 0, $enddate = 0, $groupid = 0, $showallstudents = false) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/lib/gradelib.php');
    require_once($CFG->dirroot.'/lib/grade/constants.php');
    require_once($CFG->dirroot.'/lib/grade/grade_item.php');

    $context = context_course::instance($courseid);
    $results = array();
    $gis     = array();

    // get default role
    $enrolplugins = enrol_get_instances($courseid, true);
    if (is_array($enrolplugins)) {
        foreach ($enrolplugins as $enrolplugin) {
            $enrolplugin = enrol_get_plugin($enrolplugin->enrol);
            $roleid       = $enrolplugin->get_config('roleid');
            $role         = $DB->get_record('role', array('id'=>$roleid));
            if (!empty($role)) {
                break;
            }
        }
    }

    // if we couldn't determine the default role using the enrol plugins, try to get a default role - in this case 'student'
    if (empty($role)) {
        $role = $DB->get_record('role', array('shortname' => 'student'));
        if (empty($role)) {
            print_error('couldnotgetrole', 'block_censusreport');
        }
    }

    if (!empty($groupid)) {
        $groupjoin  = 'INNER JOIN {groups_members} gm ON gm.userid = u.id ';
        $groupwhere = 'AND gm.groupid = :groupid ';
    } else {
        $groupjoin  = '';
        $groupwhere = '';
    }

    // Pass #1 - Search through grade_grades_history data for user submissions within the date range
    $sql = "SELECT u.id AS userid, u.lastname, u.firstname, u.idnumber, ggh.timemodified
              FROM {grade_items} gi
        INNER JOIN {grade_grades_history} ggh ON ggh.itemid = gi.id
        INNER JOIN {role_assignments} ra ON ra.userid = ggh.userid
        INNER JOIN {user} u ON u.id = ra.userid
        $groupjoin
             WHERE gi.courseid = :courseid
               AND gi.itemtype = 'mod'
               AND ggh.timemodified >= :timestart
               AND ggh.timemodified <= :timeend
               AND ra.contextid = :contextid
               AND ra.roleid = :roleid
               AND u.deleted = 0
               $groupwhere
          ORDER BY ggh.timemodified ASC, u.lastname ASC, u.firstname ASC";

    $dbparams = array(
        'courseid'  => $courseid,
        'timestart' => $startdate,
        'timeend'   => $enddate,
        'contextid' => $context->id,
        'roleid'    => $role->id
    );

    if (!empty($groupid)) {
        $dbparams['groupid'] = $groupid;
    }

    $rs = $DB->get_recordset_sql($sql, $dbparams);

    foreach ($rs as $user) {
        // Find the first submission by that user
        $record = bcr_check_grades_histories_initial_submission($user->userid, $startdate, $enddate, $courseid);

        if (!isset($record->giid)) {
            continue; // This shouldn't happen
        }

        $record->lastname  = $user->lastname;
        $record->firstname = $user->firstname;
        $record->idnumber  = $user->idnumber;

        if (empty($gis[$record->giid])) {
            $gis[$record->giid] = new grade_item(array('id' => $record->giid));
        }

        if (is_null($record->finalgrade)) {
            $grade = get_string('nograde', 'block_censusreport');
        } elseif (0 == $record->finalgrade) {
            $grade = '0';
        } else {
            $grade = grade_format_gradevalue($record->finalgrade, $gis[$record->giid]);
        }

        $result = new stdClass;
        $result->userid      = $record->userid;
        $result->lastname    = $record->lastname;
        $result->firstname   = $record->firstname;
        $result->student     = fullname($record);
        $result->studentid   = $record->idnumber;
        $result->activity    = $record->itemname;
        $result->grade       = $grade;
        $result->timecreated = $record->timecreated;
        $result->date        = strftime('%m/%d/%y', $record->timecreated);
        $results[$record->userid] = $result;
    }

    unset($rs);

    // Pass #2 - Get general grade item records from the DB and compare dates with grade_grades_history entries
    $sql = "SELECT gg.id, gi.id AS giid, u.id AS userid, u.firstname, u.lastname, u.idnumber, gi.itemname,
                   gg.finalgrade, gg.timecreated, gg.timemodified
              FROM {grade_items} gi
        INNER JOIN {grade_grades} gg ON gg.itemid = gi.id
        INNER JOIN {role_assignments} ra ON ra.userid = gg.userid
        INNER JOIN {user} u ON u.id = ra.userid
        $groupjoin
             WHERE gi.courseid = :courseid
               AND gi.itemtype = 'mod'
               AND gg.timemodified >= :timestart
               AND gg.timemodified <= :timeend
               AND ra.contextid = :contextid
               AND ra.roleid = :roleid
               AND u.deleted = 0
               $groupwhere
          GROUP BY gg.userid, gg.id, gi.id, u.id, u.firstname, u.lastname, u.idnumber, gi.itemname, gg.finalgrade,
                   gg.timecreated, gg.timemodified
          ORDER BY MIN(gg.timecreated) ASC, u.lastname ASC, u.firstname ASC";

    $dbparams = array(
        'contextid' => $context->id,
        'courseid'  => $courseid,
        'roleid'    => $role->id,
        'timestart' => $startdate,
        'timeend'   => $enddate
    );

    if (!empty($groupid)) {
        $dbparams['groupid'] = $groupid;
    }

    $rs = $DB->get_recordset_sql($sql, $dbparams);

    foreach ($rs as $record) {
        if (empty($gis[$record->giid])) {
            $gis[$record->giid] = new grade_item(array('id' => $record->giid));
        }

        // Some graded items only use the timemodified field and a null value for the timecreated field
        $time = $record->timemodified;

        if (empty($results[$record->userid])) {
            $result = new stdClass;
            $result->userid      = $record->userid;
            $result->lastname    = $record->lastname;
            $result->firstname   = $record->firstname;
            $result->student     = fullname($record);
            $result->studentid   = $record->idnumber;
            $result->activity    = $record->itemname;
            $result->grade       = grade_format_gradevalue($record->finalgrade, $gis[$record->giid]);
            $result->timecreated = $time;
            $result->date        = strftime('%m/%d/%y', $record->timemodified);
            $results[$record->userid] = $result;
        } else if ($time < $results[$record->userid]->timecreated && !is_null($record->finalgrade) && 0 < $record->finalgrade) {
            $results[$record->userid]->activity = $record->itemname;
            $results[$record->userid]->grade = grade_format_gradevalue($record->finalgrade, $gis[$record->giid]);
            $results[$record->userid]->timecreated = $time;
            $results[$record->userid]->date  = strftime('%m/%d/%y', $time);
        }
    }

    unset($rs);

    // Pass #3 - Get any graded forum post records from the DB
    $sql = "SELECT u.id AS userid, fp.id AS postid, gi.id AS giid, u.firstname, u.lastname, u.idnumber,
                   fp.message, gi.itemname, gg.finalgrade, fp.created AS timecreated
              FROM {forum_posts} fp
        INNER JOIN {forum_discussions} fd ON fd.id = fp.discussion
        INNER JOIN {forum} f ON f.id = fd.forum
        INNER JOIN {grade_items} gi ON gi.iteminstance = fd.forum
         LEFT JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = fp.userid)
        INNER JOIN {role_assignments} ra ON ra.userid = gg.userid
        INNER JOIN {user} u ON u.id = fp.userid
        $groupjoin
             WHERE ra.contextid = :contextid
               AND fd.course = :courseid
               AND f.assessed > 0
               AND fp.userid != 0
               AND gi.itemmodule = 'forum'
               AND fp.created >= :timestart
               AND fp.created <= :timeend
               $groupwhere
          GROUP BY fp.userid, u.id, fp.id, gi.id, u.firstname, u.lastname, u.idnumber, fp.message, gi.itemname, gg.finalgrade,
                   fp.created
          ORDER BY fp.created ASC, u.lastname ASC, u.firstname ASC";

    $dbparams = array(
        'contextid' => $context->id,
        'courseid'  => $courseid,
        'timestart' => $startdate,
        'timeend'   => $enddate
    );

    if (!empty($groupid)) {
        $dbparams['groupid'] = $groupid;
    }

    $rs = $DB->get_recordset_sql($sql, $dbparams);

    foreach ($rs as $record) {
        if (empty($gis[$record->giid])) {
            $gis[$record->giid] = new grade_item(array('id' => $record->giid));
        }

        // Only record the oldest record found.
        if (empty($results[$record->userid]) || ($record->timecreated < $results[$record->userid]->timecreated)) {
            if (empty($record->finalgrade)) {
                $grade = get_string('nograde', 'block_censusreport');
            } else {
                $grade = grade_format_gradevalue($record->finalgrade, $gis[$record->giid]);
            }

            $result = new stdClass;
            $result->userid      = $record->userid;
            $result->lastname    = $record->lastname;
            $result->firstname   = $record->firstname;
            $result->student     = fullname($record);
            $result->studentid   = $record->idnumber;
            $result->activity    = $record->itemname;
            $result->grade       = $grade;
            $result->timecreated = $record->timecreated;
            $result->date        = strftime('%m/%d/%y', $record->timecreated);
            $results[$record->userid] = $result;
        }
    }

    unset($rs);

    // Pass #4 - Get any graded glossary entries from the DB
    $sql = "SELECT u.id AS userid, ent.id AS entid, gi.id AS giid, u.firstname, u.lastname, u.idnumber, gi.itemname,
                   gg.finalgrade, ent.timecreated AS timecreated
              FROM {glossary_entries} ent
        INNER JOIN {glossary} glos ON ent.glossaryid = glos.id
        INNER JOIN {grade_items} gi ON gi.iteminstance = glos.id
         LEFT JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = ent.userid)
        INNER JOIN {role_assignments} ra ON (ra.contextid = :contextid AND ra.userid = ent.userid)
        INNER JOIN {user} u ON u.id = ent.userid
        $groupjoin
             WHERE glos.course = :courseid
               AND glos.assessed > 0
               AND ent.userid != 0
               AND gi.itemmodule = 'glossary'
               AND ent.timecreated >= :timestart
               AND ent.timecreated <= :timeend
               $groupwhere";

    $dbparams = array(
        'contextid' => $context->id,
        'courseid'  => $courseid,
        'timestart' => $startdate,
        'timeend'   => $enddate
    );

    if (!empty($groupid)) {
        $dbparams['groupid'] = $groupid;
    }

    $rs = $DB->get_recordset_sql($sql, $dbparams);

    foreach ($rs as $record) {
        if (empty($gis[$record->giid])) {
            $gis[$record->giid] = new grade_item(array('id' => $record->giid));
        }

        // Only record the oldest record found.
        if (empty($results[$record->userid]) || ($record->timecreated < $results[$record->userid]->timecreated)) {
            if (empty($record->finalgrade)) {
                $grade = get_string('nograde', 'block_censusreport');
            } else {
                $grade = grade_format_gradevalue($record->finalgrade, $gis[$record->giid]);
            }

            $result = new stdClass;
            $result->userid      = $record->userid;
            $result->lastname    = $record->lastname;
            $result->firstname   = $record->firstname;
            $result->student     = fullname($record);
            $result->studentid   = $record->idnumber;
            $result->activity    = $record->itemname;
            $result->grade       = $grade;
            $result->timecreated = $record->timecreated;
            $result->date        = strftime('%m/%d/%y', $record->timecreated);
            $results[$record->userid] = $result;
        }
    }

    unset($rs);

    // Pass #5 - Get any graded assignment entries from the DB (if they weren't in histories)
    $sql = "SELECT u.id AS userid, s.id AS entid, gi.id AS giid, u.firstname, u.lastname, u.idnumber,
                   gi.itemname, gg.finalgrade, s.timemodified AS timecreated
              FROM {assignment_submissions} s
        INNER JOIN {assignment} a ON s.assignment = a.id
        INNER JOIN {grade_items} gi ON gi.iteminstance = a.id
         LEFT JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = s.userid)
        INNER JOIN {role_assignments} ra ON (ra.contextid = :contextid AND ra.userid = s.userid)
        INNER JOIN {user} u ON u.id = s.userid
        $groupjoin
             WHERE a.course = :courseid
               AND s.userid != 0
               AND gi.itemmodule = 'assignment'
               AND s.timemodified >= :timestart
               AND s.timemodified <= :timeend
               $groupwhere";

    $dbparams = array(
        'contextid' => $context->id,
        'courseid'  => $courseid,
        'timestart' => $startdate,
        'timeend'   => $enddate
    );

    if (!empty($groupid)) {
        $dbparams['groupid'] = $groupid;
    }

    $rs = $DB->get_recordset_sql($sql, $dbparams);

    foreach ($rs as $record) {
        if (empty($gis[$record->giid])) {
            $gis[$record->giid] = new grade_item(array('id' => $record->giid));
        }

        // Only record the oldest record found.
        if (empty($results[$record->userid]) || ($record->timecreated < $results[$record->userid]->timecreated)) {
            if (empty($record->finalgrade)) {
                $grade = get_string('nograde', 'block_censusreport');
            } else {
                $grade = grade_format_gradevalue($record->finalgrade, $gis[$record->giid]);
            }

            $result = new stdClass;
            $result->userid      = $record->userid;
            $result->lastname    = $record->lastname;
            $result->firstname   = $record->firstname;
            $result->student     = fullname($record);
            $result->studentid   = $record->idnumber;
            $result->activity    = $record->itemname;
            $result->grade       = $grade;
            $result->timecreated = $record->timecreated;
            $result->date        = strftime('%m/%d/%y', $record->timecreated);
            $results[$record->userid] = $result;
        }
    }

    unset($rs);

    // Add in users without activity if desired
    if ($showallstudents === true) {
        $sql = "SELECT u.id as userid, u.lastname, u.firstname, u.idnumber
                  FROM {user} u
            INNER JOIN {role_assignments} ra ON ra.userid = u.id
            $groupjoin
                 WHERE ra.contextid = :contextid
                 $groupwhere";

        $dbparams = array(
            'contextid' => $context->id
        );

        if (!empty($groupid)) {
            $dbparams['groupid'] = $groupid;
        }

        $rs = $DB->get_recordset_sql($sql, $dbparams);

        foreach ($rs as $record) {
            if (empty($results[$record->userid])) {
                $result = new stdClass;
                $result->userid      = $record->userid;
                $result->lastname    = $record->lastname;
                $result->firstname   = $record->firstname;
                $result->student     = fullname($record);
                $result->studentid   = $record->idnumber;
                $result->activity    = get_string('noactivitycompleted', 'block_censusreport');
                $result->grade       = get_string('nograde', 'block_censusreport');
                $result->timecreated = 0;
                $result->date        = get_string('na', 'block_censusreport');
                $results[$record->userid] = $result;
            }
        }

        unset($rs);
    }

    // Sort the resulting data by using a "lastname ASC, firstname ASC" sorting algorithm
    usort($results, 'bcr_results_sort');

    return $results;
}

/**
 * This function finds the first grades_history submission made by the user
 * within the start and end dates.
 *
 * @param int - user id
 * @param int - start date timestamp
 * @param int - end date timestamp
 * @param int - course id
 * @return object - properties grade item id, user id, grade item name, final grade and time created OR and empty object if no records were found
 */
function bcr_check_grades_histories_initial_submission($userid, $startdate, $enddate, $courseid) {
    global $DB;

    $result               = new stdClass;
    $firstresult         = new stdClass; // Data struct to contain the first submission found
    $firstrun            = true;
    $usefirstsubmission = true; // Flag to denote the use of the first submission found

    // Search grades_history for the user's first submission
    $sql = "SELECT ggh.id, gi.id AS giid, ggh.userid, gi.itemname, ggh.finalgrade, ggh.timemodified AS timecreated
              FROM {grade_items} gi
        INNER JOIN {grade_grades_history} ggh ON ggh.itemid = gi.id
             WHERE ggh.userid = :userid
               AND gi.courseid = :courseid
               AND gi.itemtype = 'mod'
               AND ggh.timemodified >= :timestart
               AND ggh.timemodified < :timeend
          GROUP BY gi.id, ggh.id, ggh.userid, gi.itemname, ggh.finalgrade, ggh.timemodified
          ORDER BY ggh.timemodified ASC";

    $dbparams = array(
        'userid'    => $userid,
        'courseid'  => $courseid,
        'timestart' => $startdate,
        'timeend'   => $enddate
    );

    // If we only care about the first submission, then just fetch a single record
    if ($usefirstsubmission) {
        $rs = $DB->get_recordset_sql($sql, $dbparams, 0 ,1);
    } else {
        $rs = $DB->get_recordset_sql($sql, $dbparams);
    }

    // The code will loop through each submission made by the user
    // If a non-grade item is found then the loop is broken and the record returned
    // Else the first submission record is returned
    foreach ($rs as $record) {
        if ($firstrun) {
            // Save the first submission within the date range for two reasons:
            // 1. In case there is a second submission, within the date range
            // that does have a grade.  If this is the case then we only want to
            // save the grade value, but retain the date of the initial submission
            // 2. There are no additional submissions with a non zero/null grade within
            // the date range
            $firstresult->giid        = $record->giid;
            $firstresult->userid      = $record->userid;
            $firstresult->itemname    = $record->itemname;
            $firstresult->finalgrade  = $record->finalgrade;
            $firstresult->timecreated = $record->timecreated;

            $firstrun = false;
        }

        // If the grade is zero/null search grades_histories again for a
        // non zero/null grade matching the grade item id
        if (is_null($record->finalgrade) || 0 == $record->finalgrade) {
            // Save the zero/null grade submission, most important is the
            // date (timecreated) of the grade submission
            $result->giid        = $record->giid;
            $result->userid      = $record->userid;
            $result->itemname    = $record->itemname;
            $result->finalgrade  = $record->finalgrade;
            $result->timecreated = $record->timecreated;

            // Check for the first non zero/null submission for that grade item id
            $nonzerorecord = bcr_check_for_non_null_grade($userid, $record->giid);

            if (!empty($nonzerorecord)) {
                // a non zero/null grade submission was found. Only save the grade value
                // and break out of the loop
                $result->finalgrade   = $nonzerorecord->finalgrade;
                $usefirstsubmission = false;

                break;
            }
        } else {
            // A submission with a grade has been found, break out of the loop
            $result->giid         = $record->giid;
            $result->userid       = $record->userid;
            $result->itemname     = $record->itemname;
            $result->finalgrade   = $record->finalgrade;
            $result->timecreated  = $record->timecreated;
            $usefirstsubmission = false;

            break;
        }
    }

    unset($rs);

    // If $usefirstsubmission is true, then no additional submission were found with a
    // non zero/null grade value
    if ($usefirstsubmission) {
        return $firstresult;
    }

    return $result;
}

/**
 * This function searches the grades_history table for the first non NULL/0
 * record for a given user and grade item id
 *
 * @param int - user id
 * @param int - start date timestamp
 * @param int - end date timestamp
 * @param int - grade item id
 * @return mixed - $DB->get_record_sql object or false if no records were found
 */
function bcr_check_for_non_null_grade($userid, $gradeitemid, $startdate = null, $enddate = null) {
    global $CFG, $DB;

    $sql = "SELECT ggh.id, gi.id AS giid, ggh.userid, gi.itemname, ggh.finalgrade, ggh.timemodified AS timecreated
              FROM {grade_items} gi
        INNER JOIN {grade_grades_history} ggh ON ggh.itemid = gi.id
             WHERE ggh.userid = :userid
               AND gi.itemtype = 'mod'
               AND ggh.itemid = :itemid
               AND ggh.finalgrade > 0
               AND ggh.finalgrade IS NOT NULL";

    $params = array(
        'userid' => $userid,
        'itemid' => $gradeitemid
    );

    if (!is_null($startdate)) {
        $sql                .= " AND ggh.timemodified >= :startdate";
        $params['startdate'] = $startdate;
    }
    if (!is_null($enddate)) {
        $sql              .= " AND ggh.timemodified < :enddate";
        $params['enddate'] = $enddate;
    }
    $sql .= " ORDER BY ggh.timemodified ASC";

    return $DB->get_record_sql($sql, $params, IGNORE_MULTIPLE);
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
    $input = str_replace(array("\r", "\n", "\t"), ' ', $input);
    $input = str_replace('"', '""', $input);
    $input = '"'.$input.'"';

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
