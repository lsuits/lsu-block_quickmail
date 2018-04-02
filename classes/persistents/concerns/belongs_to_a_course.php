<?php

namespace block_quickmail\persistents\concerns;

use lang_string;
use \dml_missing_record_exception;

trait belongs_to_a_course {

    ///////////////////////////////////////////////
    ///
    ///  RELATIONSHIPS
    /// 
    ///////////////////////////////////////////////

    /**
     * Returns the course object of the persistent.
     *
     * @return stdClass
     */
    public function get_course() {
        return get_course($this->get('course_id'));
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