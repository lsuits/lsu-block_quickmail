<?php

namespace block_quickmail\persistents;

use \core\persistent;
use \lang_string;
use \dml_missing_record_exception;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_user;
use block_quickmail\persistents\concerns\can_be_soft_deleted;
 
class message extends persistent {
 
    use enhanced_persistent,
        belongs_to_a_user,
        can_be_soft_deleted;

    /** Table name for the persistent. */
    const TABLE = 'block_quickmail_messages';
 
    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'course_id' => [
                'type' => PARAM_INT,
            ],
            'user_id' => [
                'type' => PARAM_INT,
            ],
            'alternate_email_id' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'subject' => [
                'type' => PARAM_TEXT,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'body' => [
                'type' => PARAM_RAW,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'editor_format' => [
                'type' => PARAM_INT,
                'default' => 1, // @TODO - make this configurable?
            ],
            'sent_at' => [
                'type' => PARAM_INT,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'is_draft' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'timedeleted' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }
 
    ///////////////////////////////////////////////
    ///
    ///  RELATIONSHIPS
    /// 
    ///////////////////////////////////////////////

    /**
     * Returns the course object of the message.
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
     * @param int $value The value.
     * @return true|lang_string
     */
    protected function validate_course_id($value) {
        try {
            $course = get_course($value);
        } catch (dml_exception $e) {
            return new lang_string('invalidcourseid', 'error');
        }

        return true;
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    //
 
}