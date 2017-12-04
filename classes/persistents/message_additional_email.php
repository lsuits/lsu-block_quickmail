<?php

namespace block_quickmail\persistents;

use core\persistent;
use core_user;
use lang_string;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_message;
use block_quickmail\persistents\message;
 
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
                'default' => 0
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
    ///  CUSTOM METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Mark this additional email as have being sent to successfully
     * 
     * @return void
     */
    public function mark_as_sent() {
        $this->set('sent_at', time());
        $this->update();
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Deletes all additional emails for this message
     * 
     * @param  message $message
     * @return void
     */
    public static function clear_all_for_message(message $message)
    {
        global $DB;

        // delete all recipients belonging to this message
        $DB->delete_records('block_quickmail_msg_ad_email', ['message_id' => $message->get('id')]);
    }
 
}