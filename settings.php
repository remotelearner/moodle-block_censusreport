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
 * This is the settings file for the ten percent report, it handles global settings.
 *
 * @package   blocks-censusreport
 * @author    Tyler Bannister <tyler.bannister@remote-learner.net>
 * @copyright 2011 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (! defined('CHECKBOX_ARRAY')) {
    define('CHECKBOX_ARRAY', 1);
    define('CHECKBOX',       2);
    define('TEXTBOX',        3);
}

$blockname = 'block_censusreport';

$labels       = array('view' => get_string('view'),
                      'pdf'  => get_string('pdf', $blockname),
                      'csv'  => get_string('csv', $blockname));

$fields = array(
    'overrideinstances'         => CHECKBOX,
    'showallstudents'           => CHECKBOX,
    'showcoursename'            => CHECKBOX_ARRAY,
    'showcoursecode'            => CHECKBOX_ARRAY,
    'showcourseid'              => CHECKBOX_ARRAY,
    'showstudentid'             => CHECKBOX_ARRAY,
    'showteachername'           => CHECKBOX_ARRAY,
    'showsignatureline'         => CHECKBOX_ARRAY,
    'showdateline'              => CHECKBOX_ARRAY,
    'footermessage'             => TEXTBOX,
);

$legacy = array('showcoursename' => array('view','pdf'), 'showcoursecode' => array('view', 'pdf'));

foreach ($fields as $field => $type) {
    if ($field == 'showsignatureline') {
        unset($labels['view']);
    } else if ($field == 'footermessage') {
        unset($labels['csv']);
    }
    $name     = $blockname .'/'. $field;
    switch ($type) {
        case CHECKBOX_ARRAY:
            $defaults = array();

            if (isset($legacy[$field])) {
                $values = $legacy[$field];
                foreach ($values as $value) {
                    $defaults[$value] = 1;
                }
            }

            $settings->add(new admin_setting_configmulticheckbox($name, get_string($field, $blockname),
                           get_string($field .'desc', $blockname), $defaults, $labels));
            break;

        case CHECKBOX:
            $default = isset($CFG->$name) ? $CFG->$name: 0;
            $settings->add(new admin_setting_configcheckbox($name, get_string($field, $blockname),
                           get_string($field .'desc', $blockname), $default, 1, 0));
            break;

        case TEXTBOX:
        default:
            $default = isset($CFG->$name) ? $CFG->$name : '';
            $settings->add(new admin_setting_configtextarea($name, get_string($field, $blockname),
                           get_string($field .'desc', $blockname), $default, PARAM_TEXT));
            break;
    }
}