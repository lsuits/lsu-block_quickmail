<?php

namespace block_quickmail\persistents\concerns;

use core_user;
use lang_string;

trait belongs_to_a_user {

    ///////////////////////////////////////////////
    ///
    ///  RELATIONSHIPS
    /// 
    ///////////////////////////////////////////////

    /**
     * Returns the user object for this message recipient.
     *
     * @return stdClass
     */
    public function get_user() {
        return core_user::get_user($this->get('user_id'));
    }

    ///////////////////////////////////////////////
    ///
    ///  SETTERS
    /// 
    ///////////////////////////////////////////////
    
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
     * Validate the user ID.
     *
     * @param int $value The value.
     * @return true|lang_string
     */
    protected function validate_user_id($value) {
        if ( ! core_user::is_real_user($value, true)) {
            return new lang_string('invaliduserid', 'error');
        }
 
        return true;
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Convenience method to determine if this persistent is owned by the given user (or user id)
     *
     * @param object|int $idorobject The user ID, or a user object.
     * @return bool
     */
    public function is_owned_by_user($idorobject) {
        $user_id = $idorobject;
        
        if (is_object($idorobject)) {
            $user_id = $idorobject->id;
        }
        
        return $this->get('user_id') == $user_id;
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    //

}