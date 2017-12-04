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
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\renderables;

class renderable_component {

    protected $params;

    public function __construct($params = []) {
        $this->params = $params;
    }

    /**
     * Returns the given key from within the set params array, if any
     * Accepts optional second parameter, default value
     * 
     * @param  string $key
     * @param  mixed $default_value  an optional value to use if no results found
     * @return mixed
     */
    public function get_param($key, $default_value = null)
    {
        return isset($this->params[$key]) ? $this->params[$key] : $default_value;
    }

    /**
     * Returns an array of messages belonging to a specific course given an array of messages and course id
     * 
     * @param  array  $messages
     * @param  int    $course_id
     * @return array
     */
    public function filter_messages_by_course($messages, $course_id) {
        if ($course_id) {
            // if a course is selected, filter out any not belonging to the course and return
            return array_filter($messages, function($msg) use ($course_id) {
                return $msg->get('course_id') == $course_id;
            });
        }

        // otherwise, include all messages
        return $messages;
    }

    /**
     * Returns an array of user course data given an array of messages
     * This will include the currently selected course, even if that course does not have any messages
     * 
     * @param  array  $messages
     * @param  int    $selected_course_id
     * @return array  [course id => course short name]
     */
    public function get_user_course_array($messages, $selected_course_id = 0) {
        global $DB;
        
        // first, get all course ids from the given messages
        $course_ids = array_reduce($messages, function($carry, $message) {
            $carry[] = (int) $message->get('course_id');

            return $carry;
        }, []);

        // if a selected course id was given, be sure to include this course in the results
        if ($selected_course_id) {
            $course_ids[] = $selected_course_id;
        }

        // make sure we have unique values
        $course_ids = array_unique($course_ids, SORT_NUMERIC);

        // get course data for the given list of course ids
        $course_data = $DB->get_records_sql('SELECT id, shortname FROM {course} WHERE id in (' . implode(',', $course_ids) . ')');

        $results = [];

        // add an entry for each course to the results array
        foreach ($course_data as $course) {
            $results[(int) $course->id] = $course->shortname;
        }

        return $results;
    }

    /**
     * Returns a transformed array for template given a flat array of course id => course name
     * 
     * @param  array  $course_array
     * @param  int    $selected_course_id
     * @return array
     */
    public function transform_course_array($course_array, $selected_course_id = 0) {
        $results = [];

        foreach ($course_array as $id => $shortname) {
            $results[] = [
                'userCourseId' => (string) $id, 
                'userCourseName' => $shortname,
                'selectedAttr' => $selected_course_id == $id ? 'selected' : ''
            ];
        }

        return $results;
    }

}