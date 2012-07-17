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
 * @author    James McQuillan <james.mcquillan@remote-learner.net>
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
        $checkboxes = array(
                'showcoursename'    => array('view', 'csv', 'pdf'),
                'showcoursecode'    => array('view', 'csv', 'pdf'),
                'showcourseid'      => array('view', 'csv', 'pdf'),
                'showstudentid'     => array('view', 'csv', 'pdf'),
                'showteachername'   => array('view', 'csv', 'pdf'),
                'showsignatureline' => array(    '', 'csv', 'pdf'),
                'showdateline'      => array(    '', 'csv', 'pdf')
        );
        $defaults   = array('checkboxes' => 0, 'textfields' => '');

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $table_start =
            '<table>
            <tr>
                <td>'.get_string('setting', $blockname).'</td>
                <td>'.$strview.'</td>
                <td>'.$strcsv.'</td>
                <td>'.$strpdf.'</td>
            </tr>';
        $mform->addElement('html',$table_start);

        foreach ($checkboxes as $name => $row) {
            $mform->addElement('html','<tr><td>'.get_string($name, $blockname).'</td>');
            $defaults = get_config($blockname,$name);
            $defaults = (!empty($defaults)) ? explode(',',$defaults) : array();
            foreach ($row as $field) {
                $fieldname = $name.$field;
                $mform->addElement('html','<td>');
                if (!empty($field)) {
                    $mform->addElement('advcheckbox','config_'.$fieldname,'');
                    if (!isset($this->block->config->$fieldname)) {
                        $default_val = (in_array($field,$defaults) === true) ? 1 : 0;
                        $mform->setDefault('config_'.$fieldname, $default_val);
                    }
                }
                $mform->addElement('html','</td>');
            }
            $mform->addElement('html','</tr>');
        }
        $mform->addElement('html','</table>');

        $mform->addElement('advcheckbox','config_showallstudents',get_string('showallstudents', $blockname));
        if (!isset($this->block->config->showallstudents)) {
            $default = get_config($blockname,'showallstudents');
            $default = ($default == 1) ? (int)$default : 0;
            $mform->setDefault('config_showallstudents', $default);
        }

        $mform->addElement('textarea','config_footermessagepdf',get_string('footermessage', $blockname));
    }
}

