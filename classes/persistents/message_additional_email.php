<?php

namespace block_quickmail\persistents;

use core\persistent;
use core_user;
use lang_string;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_message;
 
class message_additional_email extends persistent {
 
    use enhanced_persistent, 
        belongs_to_a_message;

    /** Table name for the persistent. */
    const TABLE = 'block_quickmail_msg_ad_email';
 
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
            'email' => [
                'type' => PARAM_EMAIL,
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
    ///  VALIDATORS
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