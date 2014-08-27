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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/censusreport/lib.php');

class block_censusreport_upload_image_form extends moodleform {

    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        $imagetypes = array(
            REPORT_IMAGE_HEADER => get_string('header', 'block_censusreport'),
            REPORT_IMAGE_LOGO => get_string('logo', 'block_censusreport')
        );

        $mform->addElement('select', 'imagetype', get_string('imagetype', 'block_censusreport'), $imagetypes);

        $mform->addElement('filepicker', 'censusreportimage', '');
        $mform->addRule('censusreportimage', null, 'required', null, 'client');

        $this->add_action_buttons();
    }

    /**
     * Some validation - Based on certificate code from Michael Avelar <michaela@moodlerooms.com>
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $supportedtypes = array(
            'jpe' => 'image/jpeg',
            'jpeIE' => 'image/pjpeg',
            'jpeg' => 'image/jpeg',
            'jpegIE' => 'image/pjpeg',
            'jpg' => 'image/jpeg',
            'jpgIE' => 'image/pjpeg',
            'png' => 'image/png',
            'pngIE' => 'image/x-png'
        );

        $files = $this->get_draft_files('censusreportimage');
        if ($files) {
            foreach ($files as $file) {
                if (!in_array($file->get_mimetype(), $supportedtypes)) {
                    $errors['censusreportimage'] = get_string('invalidfiletype', 'error', $file->get_filename());
                }
            }
        } else {
            $errors['censusreportimage'] = get_string('uploadnofilefound');
        }

        return $errors;
    }
}
