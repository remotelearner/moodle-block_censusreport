<?php
// This file is part of Moodle - http://moodle.org/
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
 * PHPUnit tests for the Census Report block
 *
 * @package    block_censusreport
 * @category   phpunit
 * @copyright  2012 Remote-Learner {@link http://www.remote-learner.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__.'/../lib.php');

class block_censusreport_testcase extends advanced_testcase {

    private $startdate;
    private $enddate;

    /**
     * Setup method
     */
    protected function setUp() {
        parent::setUp();

        date_default_timezone_set('America/Toronto');
        $this->startdate = make_timestamp(2012, 4, 19, 0, 0, 0, 'America/Toronto');
        $this->enddate   = make_timestamp(2012, 4, 20, 0, 0, 0, 'America/Toronto') + DAYSECS;
    }

    /**
     * Load the testing dataset. Meant to be used by any tests that require the testing dataset.
     */
    protected function setup_db() {
        $this->loadDataSet($this->createXMLDataSet(__DIR__.'/fixtures/block_censusreport.xml'));
    }

    /**
     * Provide input data to the parameters of the test_censusreport_null_grade_check() method.
     */
    public function null_grade_check_provider() {
        return array(
            array(1, 1, false),
            array(7, 73, false),
            array(7, 76, 100.00000),
            array(8, 73, false),
            array(8, 76, 100.00000),
            array(10, 77, 91.00000),
            array(14, 74, 33.33333)
        );
    }

    /**
     * Provide input data to the parameters of the test_censusreport_initial_grade_submission() method.
     */
    public function initial_submission_provider() {
        return array(
            array(1, 1, false),
            array(7, 5, 1334938143),
            array(15, 5, 1334945636),
            array(18, 5, 1334946508),
            array(24, 5, 1334959653),
            array(28, 5, false)
        );
    }

    /**
     * Validate the individual data records that is returned from the bcr_build_grades_array() function.
     * NOTE: validation is handled via PHPUnit assert* methods.
     *
     * @param array $data           An array of data records that is used for building displayed report data
     * @param int   $expected_count The number of data records that should be contained within the data array
     */
    private function validate_report_data($data, $expected_count) {
        $this->assertTrue(is_array($data));
        $this->assertEquals($expected_count, count($data));

        foreach ($data as $datum) {
           switch ($datum->userid) {
                case 2:
                case 4:
                case 5:
                case 13:
                case 17:
                    $this->assertEquals('No Activity Completed', $datum->activity);
                    $this->assertEquals('No Grade', $datum->grade);
                    $this->assertEquals(0, $datum->timecreated);
                    break;

                case 7:
                    $this->assertEquals('Essay Question Quiz', $datum->activity);
                    $this->assertEquals(100.00, $datum->grade);
                    $this->assertEquals(1334938143, $datum->timecreated);
                    break;

                case 8:
                    $this->assertEquals('Essay Question Quiz', $datum->activity);
                    $this->assertEquals(100.00, $datum->grade);
                    $this->assertEquals(1334938325, $datum->timecreated);
                    break;

                case 9:
                    $this->assertEquals('Essay Question Quiz', $datum->activity);
                    $this->assertEquals('No Grade', $datum->grade);
                    $this->assertEquals(1334938554, $datum->timecreated);
                    break;

                case 10:
                    $this->assertEquals('Glossary', $datum->activity);
                    $this->assertEquals('91.00', $datum->grade);
                    $this->assertEquals(1334939174, $datum->timecreated);
                    break;

                case 11:
                    $this->assertEquals('Glossary', $datum->activity);
                    $this->assertEquals(15.00, $datum->grade);
                    $this->assertEquals(1334939766, $datum->timecreated);
                    break;

                case 12:
                    $this->assertEquals('Glossary', $datum->activity);
                    $this->assertEquals('No Grade', $datum->grade);
                    $this->assertEquals(1334939846, $datum->timecreated);
                    break;

                case 15:
                    $this->assertEquals('Normal Quiz', $datum->activity);
                    $this->assertEquals(66.67, $datum->grade);
                    $this->assertEquals(1334945636, $datum->timecreated);
                    break;

                case 16:
                    $this->assertEquals('Normal Quiz', $datum->activity);
                    $this->assertEquals(33.33, $datum->grade);
                    $this->assertEquals(1334946417, $datum->timecreated);
                    break;

                case 18:
                    $this->assertEquals('Normal Quiz', $datum->activity);
                    $this->assertEquals(33.33, $datum->grade);
                    $this->assertEquals(1334946508, $datum->timecreated);
                    break;

                case 19:
                    $this->assertEquals('Test Upload a single file', $datum->activity);
                    $this->assertEquals(96.00, $datum->grade);
                    $this->assertEquals(1334954044, $datum->timecreated);
                    break;

                case 20:
                    $this->assertEquals('Test Upload a single file', $datum->activity);
                    $this->assertEquals('No Grade', $datum->grade);
                    $this->assertEquals(1334954522, $datum->timecreated);
                    break;

                case 21:
                    $this->assertEquals('Test Upload a single file', $datum->activity);
                    $this->assertEquals(51.00, $datum->grade);
                    $this->assertEquals(1334959824, $datum->timecreated);
                    break;

                case 22:
                    $this->assertEquals('Test Upload a single file', $datum->activity);
                    $this->assertEquals(17.00, $datum->grade);
                    $this->assertEquals(1334959889, $datum->timecreated);
                    break;

                case 23:
                    $this->assertEquals('Test Upload a single file', $datum->activity);
                    $this->assertEquals(71.00, $datum->grade);
                    $this->assertEquals(1334959943, $datum->timecreated);
                    break;

                case 24:
                    $this->assertEquals('Test Upload a single file', $datum->activity);
                    $this->assertEquals(80.00, $datum->grade);
                    $this->assertEquals(1334959653, $datum->timecreated);
                    break;

                case 25:
                    $this->assertEquals('Forum', $datum->activity);
                    $this->assertEquals(100.00, $datum->grade);
                    $this->assertEquals(1334960469, $datum->timecreated);
                    break;

                case 26:
                    $this->assertEquals('Test Upload a single file', $datum->activity);
                    $this->assertEquals('No Grade', $datum->grade);
                    $this->assertEquals(1334959561, $datum->timecreated);
                    break;

                case 27:
                    $this->assertEquals('Online Text Assignment', $datum->activity);
                    $this->assertEquals('No Grade', $datum->grade);
                    $this->assertEquals(1334959602, $datum->timecreated);
                    break;
            }
        }
    }

    /**
     * Ensure that we can load our test dataset into the current DB.
     */
    public function test_censusreport_load_db() {
        $this->resetAfterTest(true);
        $this->setup_db();
    }

    /**
     * Validate that the bcr_csv_escape_string() method works correctly.
     */
    public function test_censusreport_csv_escape_string() {
        $input = "\r\n\t\"test value\"";

        $this->assertEquals('"   ""test value"""', bcr_csv_escape_string($input));
        $this->assertEquals('"test value"', bcr_csv_escape_string('test value'));
    }

    /**
     * Test that checking for a non-null grade history grade works correctly.
     *
     * @dataProvider null_grade_check_provider
     * @param int   $userid   The user ID to search for
     * @param int   $itemid   The grade_item ID to search for
     * @param mixed $expected The expected result (false or a float grade value)
     */
    function test_censusreport_null_grade_check($userid, $itemid, $expected) {
        $this->resetAfterTest(true);
        $this->setup_db();

        // If the expected return is false, it means a non-null grade does not exist
        if ($expected === false) {
            $this->assertFalse(bcr_check_for_non_null_grade($userid, $itemid, $this->startdate, $this->enddate));
        } else {
            $rec = bcr_check_for_non_null_grade($userid, $itemid);
            $this->assertNotEmpty($rec);
            $this->assertObjectHasAttribute('finalgrade', $rec);
            $this->assertEquals($expected, $rec->finalgrade);
        }
    }

    /**
     * Test that checking for the initial submission date in the grade history table works correctly.
     *
     * @dataProvider initial_submission_provider
     * @param int   $userid   The user ID to search for
     * @param int   $courseid The course ID to search for
     * @param mixed $expected The expected result (false or a int UNIX timestamp value)
     */
    public function test_censusreport_initial_grade_submission($userid, $courseid, $expected) {
        $this->resetAfterTest(true);
        $this->setup_db();

        if ($expected === false) {
            $rec = bcr_check_grades_histories_initial_submission($userid, $this->startdate, $this->enddate, $courseid);
            $this->assertFalse(isset($rec->timecreated));
        } else {
            $rec = bcr_check_grades_histories_initial_submission($userid, $this->startdate, $this->enddate, $courseid);
            $this->assertNotEmpty($rec);
            $this->assertObjectHasAttribute('timecreated', $rec);
            $this->assertEquals($expected, $rec->timecreated);
        }
    }

    /**
     * Test that the function to build the report data for all users in a course works correctly.
     */
    public function test_censusreport_build_grade_array_all_users() {
        $this->resetAfterTest(true);
        $this->setup_db();

        // Get all userss within the course
        $context = context_course::instance(5);
        $userids = get_users_by_capability($context, 'moodle/course:isincompletionreports', 'u.id', 'u.lastname ASC, u.firstname ASC', '', '', '', '', false, true);

        if (!empty($userids)) {
            $userids = array_keys($userids);
        }

        $data = bcr_build_grades_array(5, $userids, $this->startdate, $this->enddate, 0, true);
        $this->validate_report_data($data, 24);
    }

    /**
     * Test that the function to build the report data for a specific group of users in a course works correctly.
     */
    public function test_censusreport_build_grade_array_groups() {
        $this->resetAfterTest(true);
        $this->setup_db();

        // Perform the test with group ID 1
        $userids = groups_get_members(1, 'u.id');

        if (!empty($userids)) {
            $userids = array_keys($userids);
        }

        $data = bcr_build_grades_array(5, $userids, $this->startdate, $this->enddate, 1, true);
        $this->validate_report_data($data, 10);

        // Perform the test with group ID 2
        $userids = groups_get_members(2, 'u.id');

        if (!empty($userids)) {
            $userids = array_keys($userids);
        }

        $data = bcr_build_grades_array(5, $userids, $this->startdate, $this->enddate, 2, true);
        $this->validate_report_data($data, 11);
    }
}
