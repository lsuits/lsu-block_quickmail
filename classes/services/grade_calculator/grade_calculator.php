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

class grade_calculator {

    public $course_id;
    public $user_id;
    public $context;
    public $report;
    public $dom;

    public function __construct($course_id, $user_id) {
        $this->course_id = $course_id;
        $this->user_id = $user_id;
        $this->set_context();
        $this->set_report();
        $this->set_dom();
    }
    
    /**
     * Returns a given user's grade of a given type in a given course
     * @param  int     $course_id
     * @param  int     $user_id
     * @param  string  $type        raw|range|percentage
     * @return mixed
     */
    public static function get_user_grade_in_course($course_id, $user_id, $type)
    {
        $calc = new self($course_id, $user_id);

        return $calc->get_course_grade_value_by_type($type);
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
     * Returns a course grade value of the given type from the user report
     * 
     * @param  string  $type
     * @return mixed
     */
    private function get_course_grade_value_by_type($type)
    {
        $elements = $this->get_course_grade_nodes();

        // get the element that corresponds to the given grade type
        $element = $elements->item($elements->length - $this->get_grade_index_offset_by_type($type));

        // return the content of this element
        return $element->textContent;
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

    /**
     * Returns an index offset for the given type, defaulting to raw
     * 
     * @param  string  $type
     * @return int
     */
    private function get_grade_index_offset_by_type($type)
    {
        switch ($type) {
            case 'percentage':
                return 3;
                break;

            case 'range':
                return 4;
                break;
            
            default:
            case 'raw':
                return 5;
                break;
        }
    }

}