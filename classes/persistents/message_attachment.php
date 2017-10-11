<?php

namespace block_quickmail\persistents;

use core\persistent;
use core_user;
use lang_string;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_message;
 
class message_attachment extends persistent {
 
    use enhanced_persistent, 
        belongs_to_a_message;

    /** Table name for the persistent. */
    const TABLE = 'block_quickmail_msg_attach';
 
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
            'filename' => [
                'type' => PARAM_FILE,
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