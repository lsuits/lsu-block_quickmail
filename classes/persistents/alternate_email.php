<?php

namespace block_quickmail\persistents;

use core\persistent;
use core_user;
use lang_string;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\can_be_soft_deleted;
 
class alternate_email extends persistent {
 
    use enhanced_persistent,
        can_be_soft_deleted;

    /** Table name for the persistent. */
    const TABLE = 'block_quickmail_alt_emails';
 
    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'setup_user_id' => [
                'type' => PARAM_INT,
            ],
            'firstname' => [
                'type' => PARAM_TEXT,
            ],
            'lastname' => [
                'type' => PARAM_TEXT,
            ],
            'course_id' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'user_id' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'email' => [
                'type' => PARAM_EMAIL,
            ],
            'is_validated' => [
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
     * Returns the user object for the user who created this alternate email
     *
     * @return stdClass
     */
    public function get_setup_user() {
        return core_user::get_user($this->get('setup_user_id'));
    }

    /**
     * Returns the course object of this alternate email.
     *
     * @return stdClass|false
     */
    public function get_course() {
        $courseId = $this->get('course_id');

        return $courseId ? get_course($this->get('course_id')) : false;
    }

    /**
     * Returns the user object of this alternate email.
     *
     * @return stdClass|false
     */
    public function get_user() {
        $userId = $this->get('user_id');

        return $userId ? core_user::get_user($this->get('user_id')) : false;
    }

    ///////////////////////////////////////////////
    ///
    ///  GETTERS
    /// 
    ///////////////////////////////////////////////
    
    /**
     * Returns the status of this alternates approval (approved or waiting)
     * 
     * @return string
     */
    public function get_status() {
        return $this->get('is_validated') ?
            \block_quickmail_plugin::_s('approved') :
            \block_quickmail_plugin::_s('waiting');
    }

    /**
     * Returns the full name assigned to this alternate
     * 
     * @return string
     */
    public function get_fullname() {
        return $this->get('firstname') . ' ' .  $this->get('lastname');
    }

    ///////////////////////////////////////////////
    ///
    ///  SETTERS
    /// 
    ///////////////////////////////////////////////
    
    /**
     * Convenience method to set the "setup" user ID.
     *
     * @param object|int $idorobject The user ID, or a user object.
     */
    protected function set_setup_user_id($idorobject) {
        $user_id = $idorobject;
        
        if (is_object($idorobject)) {
            $user_id = $idorobject->id;
        }
        
        $this->raw_set('setup_user_id', $user_id);
    }

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

    /**
     * Convenience method to set the user ID.
     *
     * @param object|int $idorobject The user ID, or a user object.
     */
    protected function set_user_id($idorobject) {
        $user_id = $idorobject;
        
        if (is_object($idorobject)) {
            $user_id = $idorobject->id;
        }
        
        $this->raw_set('user_id', $user_id);
    }

    ///////////////////////////////////////////////
    ///
    ///  VALIDATORS
    /// 
    ///////////////////////////////////////////////

    /**
     * Validate the setup user ID.
     *
     * @param int $value The value.
     * @return true|lang_string
     */
    protected function validate_setup_user_id($value) {
        if ( ! core_user::is_real_user($value, true)) {
            return new lang_string('invaliduserid', 'error');
        }
 
        return true;
    }

    /**
     * Validate the course ID.
     *
     * @param int $value The value.
     * @return true|lang_string
     */
    protected function validate_course_id($value) {
        if ( ! $value) {
            return true;
        }

        try {
            $course = get_course($value);
        } catch (dml_exception $e) {
            return new lang_string('invalidcourseid', 'error');
        }

        return true;
    }

    /**
     * Validate the user ID.
     *
     * @param int $value The value.
     * @return true|lang_string
     */
    protected function validate_user_id($value) {
        if ( ! $value) {
            return true;
        }

        if ( ! core_user::is_real_user($value, true)) {
            return new lang_string('invaliduserid', 'error');
        }
 
        return true;
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Returns all alternate emails belonging to the given user id
     * 
     * @param  int     $user_id
     * @return array
     */
    public static function get_all_for_user($user_id)
    {
        // get all alternate emails set up by this user, if any
        return self::get_records(['setup_user_id' => $user_id, 'timedeleted' => 0]);
    }
 
}