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
 * @author    James McQuillan <james.mcquillan@remote-learner.net>
 * @copyright 2009 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

/// Check for valid admin user


$cid        = optional_param('id', SITEID, PARAM_INT);        // Course ID
$action     = optional_param('action', 'setup', PARAM_INT); // Action
$instanceid = optional_param('instanceid', 0, PARAM_INT);     // Instance id

if ($cid == SITEID) {
    require_login();
    $context = context_system::instance(0);
    $capability   = 'block/censusreport:accessallreports';
} else {
    if (! $course = $DB->get_record("course", array("id"=>$cid))) {
        print_error('cannotfindcourse');
    }
    require_login($course);
    $context = context_course::instance($cid);
    $capability   = 'block/censusreport:accesscoursereport';
}

require_capability($capability, $context);
$PAGE->set_url('/blocks/censusreport/report.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('setupquery', 'block_censusreport'));
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add(get_string('reportlink', 'block_censusreport'));

$mform = new bcr_setup_query_form($PAGE->url, $instanceid, $cid);

if ($mform->is_cancelled()){
    redirect($CFG->wwwroot.'/course/view.php?id='.$cid);
    die();
}

if ($formdata = $mform->get_data()) {

    $instance = null;
    if ($instanceid > 0) {
        $instance      = $DB->get_record('block_instances', array('id'=>$instanceid));
    }
    $blockinstance = block_instance('censusreport', $instance);
    if ($action == CENSUS_ACTION_DLPDF) {
        $type = 'pdf';
    } else if ($action == CENSUS_ACTION_DLCSV) {
        $type = 'csv';
    } else {
        $type = 'view';
    }

    if (!empty($formdata->enddate)) {
        $formdata->enddate+=DAYSECS;
    }

    if ($type === 'view') {
        echo $OUTPUT->header();
        echo $OUTPUT->box_start();
    }

    if (bcr_generate_report($blockinstance, $formdata, $type) === false) {
        if ($type !== 'view') {
            echo $OUTPUT->header();
            echo $OUTPUT->box_start();
            $mform->display();
        }
        notify(get_string('nodatafound','block_censusreport'), 'notifysuccess');
    }

    if ($type === 'view') {
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
    }

} else {
    echo $OUTPUT->header();
    echo $OUTPUT->box_start();

    $mform->display();

    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
}