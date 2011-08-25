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
 * Handles the setup and display of the report.
 *
 * @package   blocks-censusreport
 * @author    Justin Filip <jfilip@remote-learner.net>
 * @copyright 2009 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

/// Check for valid admin user
require_login();

$cid        = optional_param('id', SITEID, PARAM_INT);        // Course ID
$action     = optional_param('action', 'setup', PARAM_ALPHA); // Action
$course     = optional_param('course', 0, PARAM_INT);         // Selected course id
$instanceid = optional_param('instanceid', 0, PARAM_INT);     // Instance id

$contextlevel = CONTEXT_COURSE;
$capability   = 'block/censusreport:accesscoursereport';
if ($cid == SITEID) {
    $contextlevel = CONTEXT_SYSTEM;
    $capability   = 'block/censusreport:accessallreports';
}

$context = get_context_instance($contextlevel, $cid);
require_capability($capability, $context);

$act_getreport         = eregi_replace('[^a-zA-Z]', '', get_string('getreport','block_censusreport'));
$act_downloadreportpdf = eregi_replace('[^a-zA-Z]', '', get_string('downloadreportpdf','block_censusreport'));
$act_downloadreportcsv = eregi_replace('[^a-zA-Z]', '', get_string('downloadreportcsv','block_censusreport'));
$act_exit              = eregi_replace('[^a-zA-Z]', '', get_string('exit','block_censusreport'));

if ($formdata = data_submitted()) {
//        print_object($formdata);
}

if ($cid == SITEID) {
    $repfunction = 'bcr_setup_site_query';
} else {
    $repfunction = 'bcr_setup_course_query';
}

$message = '';

switch ($action) {
    case $act_exit:
        redirect($CFG->wwwroot.'/course/view.php?id='.$cid);
        break;

    case $act_getreport:
    case $act_downloadreportpdf:
    case $act_downloadreportcsv:
        $formdata->startdate = mktime(0,0,0,$formdata->sincemonth,$formdata->sinceday,$formdata->sinceyear);
        $formdata->enddate   = mktime(0,0,0,$formdata->tomonth,$formdata->today,$formdata->toyear) + DAYSECS;
        $type = 'view';
        $instance = null;

        if ($instanceid > 0) {
            $instance      = get_record('block_instance', 'id', $instanceid);
        }
        $blockinstance = block_instance('censusreport', $instance);

        if ($action == $act_downloadreportpdf) {
            $type = 'pdf';
        } else if ($action == $act_downloadreportcsv) {
            $type = 'csv';
        }

        if (bcr_generate_report($blockinstance, $formdata, $type) === false) {
            $repfunction($cid, $instanceid, $formdata, 'No data found');
        }

        break;

    case 'setup':
    default:
        $repfunction($cid, $instanceid, $formdata, $message);
}

?>