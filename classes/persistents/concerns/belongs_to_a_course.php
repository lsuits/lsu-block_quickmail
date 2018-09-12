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

namespace block_quickmail\persistents\concerns;

use lang_string;

trait belongs_to_a_course {

    ///////////////////////////////////////////////
    ///
    ///  RELATIONSHIPS
    /// 
    ///////////////////////////////////////////////

    /**
     * Returns the course object of the persistent, defaulting to null if no course
     *
     * @return stdClass
     */
    public function get_course() {
        try {
            return get_course($this->get('course_id'));
        } catch (\Exception $e) {
            return null;
        }
    }

    ///////////////////////////////////////////////
    ///
    ///  GETTERS
    /// 
    ///////////////////////////////////////////////

    /**
     * Returns a given property value of the this persistent's course object, defaulting to a given default value
     * 
     * @param  string  $property  name of a course property
     * @param  mixed   $default   default value to return if cannot be retrieved
     * @return mixed
     */
    public function get_course_property($property, $default = null) {

        if ($course = $this->get_course()) {
            if (property_exists($course, $property)) {
                return $course->$property;
            }
        }
        
        return $default;
    }

    ///////////////////////////////////////////////
    ///
    ///  SETTERS
    /// 
    ///////////////////////////////////////////////
    
    /**
     * Convenience method to set the course ID.
     *
     * @param object|int $idorobject The course ID, or a course object.
     */
    protected function set_course_id($idorobject) {
        $course_id = $idorobject;
        
        if (is_object($idorobject)) {
            $course_id = $idorobject->id;
        }
        
        $this->raw_set('course_id', $course_id);
    }

    ///////////////////////////////////////////////
    ///
    ///  VALIDATORS
    /// 
    ///////////////////////////////////////////////

    /**
     * Validate the course ID.
     *
     * NOTE: turning this off for now as validation should happen externally before this method is ever called!!!
     *
     * @param int $value The value.
     * @return true|lang_string
     */
    // protected function validate_course_id($value) {
    //     try {
    //         $course = get_course($value);
    //     } catch (dml_exception $e) {
    //         return new lang_string('invalidcourseid', 'error');
    //     }

    //     return true;
    // }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Convenience method to determine if this persistent is owned by the given course (or course id)
     *
     * @param object|int $idorobject The course ID, or a course object.
     * @return bool
     */
    public function is_owned_by_course($idorobject) {
        $course_id = $idorobject;
        
        if (is_object($idorobject)) {
            $course_id = $idorobject->id;
        }
        
        return $this->get('course_id') == $course_id;
    }

}