<?php

namespace block_quickmail\persistents;

use core\persistent;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_message;
use block_quickmail\persistents\concerns\belongs_to_a_user;
 
class message_recipient extends persistent {
 
    use enhanced_persistent, 
        belongs_to_a_message,
        belongs_to_a_user;

    /** Table name for the persistent. */
    const TABLE = 'block_quickmail_msg_recips';
 
    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'message_id' => [
                'type' => PARAM_INT,
            ],
            'user_id' => [
                'type' => PARAM_INT,
            ],
            'sent_at' => [
                'type' => PARAM_INT,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
        ];
    }
 
    ///////////////////////////////////////////////
    ///
    ///  RELATIONSHIPS
    /// 
    ///////////////////////////////////////////////

    //

    ///////////////////////////////////////////////
    ///
    ///  SETTERS
    /// 
    ///////////////////////////////////////////////
    
    //

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    //
 
}