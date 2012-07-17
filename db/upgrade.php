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
 * Upgrade script to handle any database or other changes required as
 * part of a new block version.
 *
 * @package   blocks-censusreport
 * @author    Justin Filip <jfilip@remote-learner.net>
 * @copyright 2009 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function xmldb_block_censusreport_upgrade($oldversion = 0) {
    global $CFG;
    $result = true;


    if ($oldversion < 2012051801) {
        $blockname = 'block_censusreport';
        foreach ($CFG as $cfgkey => $val) {
            if (strpos($cfgkey,$blockname) === 0) {
                $pluginkey = substr($cfgkey,strlen($blockname)+1);
                set_config($pluginkey,$val,$blockname);
                unset_config($cfgkey);
            }
        }
    }
    return $result;
}
