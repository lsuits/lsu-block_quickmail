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

namespace block_quickmail\services\grade_calculator;

class course_grade_report extends \grade_report {

    public $user;

    /**
     * show course/category totals if they contain hidden items
     */
    var $showtotalsifcontainhidden;

    public function __construct($course_id, $course_context, $user_id) {
        parent::__construct($course_id, null, $course_context);

        global $DB, $CFG;

        // get the user to be graded
        $this->user = $DB->get_record('user', ['id' => $user_id], '*', MUST_EXIST);

        // necessary for parent report
        $this->showtotalsifcontainhidden[$course_id] = grade_get_setting($course_id, 'report_overview_showtotalsifcontainhidden', $CFG->grade_report_overview_showtotalsifcontainhidden);
    }

    // necessary for implementation of \grade_report abstract
    function process_action($target, $action) {}
    
    // necessary for implementation of \grade_report abstract
    function process_data($data)
    {
        return $this->screen->process($data);
    }
    
    // getter for the \grade_report's method
    function get_blank_hidden_total_and_adjust_bounds($course_id, $course_total_item, $finalgrade)
    {
        return $this->blank_hidden_total_and_adjust_bounds($course_id, $course_total_item, $finalgrade);
    }

}