<?php

// This file is part of the Censusreport module for Moodle - http://moodle.org/
//
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
 * Handles uploading files
 *
 * @package    block_censusreport
 * @subpackage censusreport
 * @copyright  2013 Remote-Learner <http://www.remote-learner.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/blocks/censusreport/lib.php');
require_once($CFG->dirroot.'/blocks/censusreport/upload_image_form.php');

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$struploadimage = get_string('uploadimage', 'block_censusreport');
$struploaderror = get_string('uploaderror', 'block_censusreport');

$PAGE->set_url('/admin/settings.php', array('section' => 'blocksettingcensusreport'));
$PAGE->set_pagetype('admin-setting-blocksettingcensusreport');
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_title($struploadimage);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($struploadimage);

$upload_form = new block_censusreport_upload_image_form();

if ($upload_form->is_cancelled()) {
    redirect(new moodle_url('/admin/settings.php?section=blocksettingcensusreport'));
} else if ($data = $upload_form->get_data()) {
    // Ensure the directory for storing is created
    $uploaddir = "blocks/censusreport/pix/$data->imagetype";
    $filename = $upload_form->get_new_filename('censusreportimage');
    make_upload_directory($uploaddir);
    $destination = $CFG->dataroot.'/'.$uploaddir.'/'.$filename;
    if (!$upload_form->save_file('censusreportimage', $destination, true)) {
        print_error($struploaderror);
    }

    redirect(new moodle_url('/admin/settings.php?section=blocksettingcensusreport'), get_string('changessaved'));
}

echo $OUTPUT->header();
echo $upload_form->display();
echo $OUTPUT->footer();
