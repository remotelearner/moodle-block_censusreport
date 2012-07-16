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
 * The is the block file for the tenpercent report, it handles displaying the block contents.
 *
 * @package   blocks-censusreport
 * @author    Justin Filip <jfilip@remote-learner.net>
 * @author    James McQuillan <james.mcquillan@remote-learner.net>
 * @copyright 2011 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Tenpercent report block object
 *
 * @author    Justin Filip <jfilip@remote-learner.net>
 * @copyright 2011 Remote Learner - http://www.remote-learner.net/
 */
class block_censusreport extends block_base {

    /**
     * Init function
     */
    function init() {
        $this->title   = get_string('pluginname','block_censusreport');
    }


    /**
     * Get block content
     *
     * @return string The block content
     * @uses $CFG
     */
    function get_content() {
        global $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';

        $access = false;

        if ($this->page->course->id == SITEID) {
            $access = has_capability('block/censusreport:accessallreports', get_context_instance(CONTEXT_SYSTEM));
        } else {
            $access = has_capability('block/censusreport:accesscoursereport', $this->page->context);
        }

        if ($access) {
            $this->content->text .= '<img src="'.$CFG->wwwroot.'/blocks/censusreport/pix/report.gif" /> ';
            $this->content->text .= '<a href="'.$CFG->wwwroot.'/blocks/censusreport/report.php?id='
                                      . $this->page->course->id .'&amp;instanceid='. $this->instance->id .'">'.
                                        get_string('reportlink', 'block_censusreport').'</a>';
        }

        $this->content->footer = '';
        return $this->content;
    }

    /**
     * Allow instance configuration
     *
     * @return bool true
     */
    function instance_allow_config() {
        return true;
    }

    /**
     * Allow global configuration
     *
     * @return bool true
     */
    function has_config() {
        return true;
    }

    /**
     * Checks whether we're supposed to show a field
     *
     * @param string $field The field to check
     * @return bool Whether to show the field
     * @uses $CFG
     */
    function check_field_status($field, $type='') {
        $blockname     = 'block_censusreport';
        $blockoverride = 'overrideinstances';
        $status        = false;

        $gbl_config = get_config($blockname);
        if (!empty($gbl_config->$blockoverride)) {
            if (!empty($gbl_config->$field) && ($type != '')) {
                $values = explode(',', $gbl_config->$field);
                foreach ($values as $value) {
                    if ($value == $type) {
                        return true;
                    }
                }
            }
        } else {
            $configname = $field.$type;
            if (!empty($this->config->$configname)) {
                $status = true;
            }
        }

        return $status;
    }
}