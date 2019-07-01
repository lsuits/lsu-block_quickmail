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
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\services\grade_calculator\course_grade_calculator;

global $CFG;
require_once($CFG->libdir . '/gradelib.php');

class block_quickmail_grade_calculator_testcase extends advanced_testcase {

    use has_general_helpers,
        sets_up_courses;

    public function test_calculates_a_students_grade_in_a_course() {
        $this->resetAfterTest(true);

        // Set up a course with a teacher and 4 students.
        list($course, $userteacher, $userstudents) = $this->setup_course_with_teacher_and_students();
        $cgc = course_grade_calculator::for_course($course->id);

        $student = $userstudents[0];

        $gi1 = new grade_item($this->dg()->create_grade_item(['courseid' => $course->id]), false);
        $gi2 = new grade_item($this->dg()->create_grade_item(['courseid' => $course->id]), false);

        // Grade the first item for the student.
        $gi1->update_final_grade($student->id, 20, 'test');

        // Grade the second item for the student.
        $gi2->update_final_grade($student->id, 40, 'test');

        // This does not work, need to recalculate grades here?
        $grade = $cgc->get_user_course_grade($student->id, 'round');

        $this->dd($grade);
    }

}
