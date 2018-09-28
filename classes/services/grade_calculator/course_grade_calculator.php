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

use block_quickmail\services\grade_calculator\course_grade_report;
use block_quickmail\services\grade_calculator\calculation_exception;

require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/report/lib.php');

class course_grade_calculator {

    public $course_id;
    public $course_context;
    public $course_grade_item;

    /**
     * Constructs the course grade calculator
     *
     * @param int  $course_id    course id
     */
    public function __construct($course_id) {
        $this->course_id = $course_id;
        $this->set_context();
        $this->set_grade_item();
    }

    /**
     * Returns a grade calculator for the given course, defaulting to null if cannot be
     * calculated
     * 
     * @param  int  $course_id
     * @return self|null
     */
    public static function for_course($course_id)
    {
        try {
            return new self($course_id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Returns a final course total grade value for the given user in the given format
     * 
     * @param  int     $user_id
     * @param  string  $display_type  real|percentage|letter|round
     * @return mixed
     */
    public function get_user_course_grade($user_id, $display_type = 'round')
    {
        if ($this->course_grade_item->hidden) {
            $this->throw_calculation_exception($user_id);
        }

        $user_grade_grade = new \grade_grade([
            'itemid' => $this->course_grade_item->id,
            'userid' => $user_id
        ]);

        $user_grade_grade->grade_item =& $this->course_grade_item;

        $finalgrade = $user_grade_grade->finalgrade;

        $report = $this->get_course_grade_report_for_user($user_id);

        if ( ! has_capability('moodle/grade:viewhidden', $this->course_context, $user_id) and ! is_null($finalgrade)) {
            $adjustedgrade = $report->get_blank_hidden_total_and_adjust_bounds(
                $this->course_id, 
                $this->course_grade_item, 
                $finalgrade
            );
            
            $this->course_grade_item->grademax = $adjustedgrade['grademax'];
            $this->course_grade_item->grademin = $adjustedgrade['grademin'];
        } else if ( ! is_null($finalgrade)) {
            $adjustedgrade = $report->get_blank_hidden_total_and_adjust_bounds(
                $this->course_id, 
                $this->course_grade_item, 
                $finalgrade
            );
            
            $this->course_grade_item->grademin = $user_grade_grade->get_grade_min();
            $this->course_grade_item->grademax = $user_grade_grade->get_grade_max();
        }
        
        if ( ! isset($adjustedgrade)) {
            $this->throw_calculation_exception($user_id);
        }

        $use_localized_decimal = false;
        $display_decimals = null;

        $totalgrade = grade_format_gradevalue($adjustedgrade['grade'], $this->course_grade_item, $use_localized_decimal, $this->get_display_type($display_type), $display_decimals);

        // if the requested display type is "round", round the value to no decimal places
        if ($display_type == 'round') {
            $explode = explode(' ', $totalgrade);
            $first = reset($explode);
            $totalgrade = (int) round($first, 0, PHP_ROUND_HALF_DOWN);
        }
        
        return $totalgrade;
    }

    /**
     * Sets the course context
     */
    private function set_context()
    {
        $this->course_context = \context_course::instance($this->course_id);
    }

    /**
     * Sets a the course total grade item for this course
     *
     * @throws calculation_exception
     */
    private function set_grade_item()
    {
        try {
            if ( ! $course_grade_item = \grade_item::fetch_course_item($this->course_id)) {
                throw new \Exception;
            }
            
            $this->course_grade_item = $course_grade_item;
        } catch (\Exception $e) {
            $this->throw_calculation_exception(null, 'Could not fetch the grade item for the course.');
        }
    }

    /**
     * Returns an instantiated extension of the grade report for this course and user
     * 
     * @param  int  $user_id
     * @return course_grade_report
     * @throws calculation_exception
     */
    private function get_course_grade_report_for_user($user_id)
    {
        try {
            return new course_grade_report($this->course_id, $this->course_context, $user_id);
        } catch (\Exception $e) {
            $this->throw_calculation_exception($user_id);
        }
    }

    /**
     * Returns the moodle constant for the given short type display
     * 
     * @param  string  $type  real|percentage|letter|round
     * @return const
     */
    private function get_display_type($type)
    {
        switch ($type) {
            case 'real':
                return GRADE_DISPLAY_TYPE_REAL;
                break;

            case 'letter':
                return GRADE_DISPLAY_TYPE_LETTER;
                break;

            case 'percentage':
            case 'round':
            default:
                return GRADE_DISPLAY_TYPE_PERCENTAGE;
                break;
        }
    }

    /**
     * Throw a calculation exception with the given message
     * 
     * @param  mixed|int  $user_id   optional, defaulting to null
     * @param  string     $message
     * @return void
     * @throws calculation_exception
     */
    private function throw_calculation_exception($user_id = null, $message = 'Could not calculate final course grade for this user.')
    {
        throw new calculation_exception('Could not fetch the grade item for the course.', $this->course_id, $user_id);
    }

}