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
 * This is the settings file for the census report, it handles global settings.
 *
 * @package   blocks-censusreport
 * @author    Tyler Bannister <tyler.bannister@remote-learner.net>
 * @copyright 2011 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_censusreport_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG;

        $strview = get_string('view', 'block_censusreport');
        $strcsv  = get_string('csv', 'block_censusreport');
        $strpdf  = get_string('pdf', 'block_censusreport');
        $blockname  = 'block_censusreport';
        $fields = array(
            'checkboxes' => array(
                'showcoursename'    => array('view', 'csv', 'pdf'),
                'showcoursecode'    => array('view', 'csv', 'pdf'),
                'showcourseid'      => array('view', 'csv', 'pdf'),
                'showstudentid'     => array('view', 'csv', 'pdf'),
                'showteachername'   => array('view', 'csv', 'pdf'),
                'showsignatureline' => array(    '', 'csv', 'pdf'),
                'showdateline'      => array(    '', 'csv', 'pdf')),
            'textfields' => array(
                'footermessage'     => array(    '',    '', 'pdf'))
        );
        $defaults   = array('checkboxes' => 0, 'textfields' => '');

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        if (isset($CFG->block_censusreport_showcoursename)) {
            $values = explode(',', $CFG->block_censusreport_showcoursename);
            foreach ($values as $value) {
                $defaults[$value] = 1;
            }
        }
        if (isset($this->config->showcoursenameview)) {
            $default = $this->config->showcoursenameview;
        } else if (isset($defaults['view'])) {
            $default = $defaults['view'];
        } else {
            $default = 0;
        }
        $mform->addElement('checkbox', 'showcoursenameview', get_string('showcoursename', 'block_censusreport'), $strview);
        $mform->setDefault('showcoursenameview', $default);

        $mform->addElement('checkbox', 'showcoursenamepdf', '', $strpdf);
        $mform->setDefault('showcoursenamepdf', $default);

    }
}

