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

use block_quickmail\services\grade_calculator\value_type_not_found_exception;
use block_quickmail\services\grade_calculator\calculation_exception;

class grade_calculator {

    public $course_id;
    public $user_id;
    public $value_type;
    public $context;
    public $report;
    public $dom;

    /**
     * Constructs the grade calculator
     *
     * @param int  $course_id    course id
     * @param int  $user_id      user id
     * @param int  $value_type   weight|grade|range|percentage|feedback|round
     */
    public function __construct($course_id, $user_id, $value_type) {
        $this->course_id = $course_id;
        $this->user_id = $user_id;
        $this->value_type = $value_type;
        $this->set_context();
        $this->set_report();
        $this->set_dom();
    }
    
    /**
     * Returns a given user's grade of a given type in a given course
     * 
     * @param  int     $course_id
     * @param  int     $user_id
     * @param  string  $value_type        weight|grade|range|percentage|feedback|round
     * @return mixed
     * @throws block_quickmail\services\grade_calculator\value_type_not_found_exception
     */
    public static function get_user_grade_in_course($course_id, $user_id, $value_type)
    {
        $given_value_type = $value_type;

        // if the given type is "round", fetch as percentage first
        if ($given_value_type == 'round') {
            $value_type = 'percentage';
        }

        try {
            $calc = new self($course_id, $user_id, $value_type);
            
            // fetch the grade value for the given type
            $value = $calc->get_course_grade_value_by_type();
        } catch (\Exception $e) {
            throw new calculation_exception('Could not calculate the ' . $this->value_type . ' grade value for this course user.', $this->course_id, $this->user_id);
        }
        
        // if the requested value type is "round", round the value to no decimal places
        if ($given_value_type == 'round') {
            $explode = explode(' ', $value);

            $first = reset($explode);

            $value = (int) round($first, 0, PHP_ROUND_HALF_DOWN);
        }

        return $value;
    }

    /**
     * Sets the course context
     */
    private function set_context()
    {
        $this->context = \context_course::instance($this->course_id);
    }

    /**
     * Sets the grade report for this user
     */
    private function set_report()
    {
        global $CFG;
        
        require_once $CFG->dirroot.'/grade/lib.php';
        require_once $CFG->dirroot.'/grade/report/user/lib.php';
        
        $gpr = new \grade_plugin_return([
            'type' => 'report', 
            'plugin' => 'user', 
            'courseid' => $this->course_id, 
            'userid' => $this->user_id
        ]);

        $this->report = new \grade_report_user($this->course_id, $gpr, $this->context, $this->user_id);
    }

    /**
     * Sets the dom which is a DOMDocument class containing the html for this grade report
     */
    private function set_dom()
    {
        $this->dom = new \DOMDocument();

        $this->report->fill_table();

        $this->dom->loadHTML($this->report->print_table(true));
    }

    /**
     * Returns a course grade value for the set type from the user report
     * 
     * @return mixed
     */
    private function get_course_grade_value_by_type()
    {
        $node_list = $this->get_course_grade_nodes();

        $element = $this->get_course_grade_element_of_type($node_list);

        // return the content of this element
        return $element->textContent;
    }

    /**
     * Iterates through the given element list, returning the element which contains a
     * 'headers' attribute that includes the set value type
     * 
     * @param  \DOMNodeList  $elements
     * @return \DOMElement
     */
    private function get_course_grade_element_of_type($elements)
    {
        foreach ($elements as $element) {
            $header_attr_string = $element->getAttribute('headers');

            $attrs = explode(' ', $header_attr_string);

            if (in_array($this->value_type, $attrs)) {
                return $element;
            }
        }

        throw new value_type_not_found_exception('A ' . $this->value_type . ' was not found for this course user.', $this->course_id, $this->user_id);
    }

    /**
     * Returns a list of course grade elements from the dom
     * 
     * @return \DOMNodeList
     */
    public function get_course_grade_nodes()
    {
        // get the tbody dom node element
        $tbody_list = $this->dom->getElementsByTagName('tbody');
        $tbody_node = $tbody_list->item(0);

        // get the last tr dom node element of this tbody
        $last_tr_node = $tbody_node->childNodes->item($tbody_node->childNodes->length - 1);

        // return a list of this tr's td dom elements
        return $last_tr_node->getElementsByTagName('td');
    }

}