<?php

namespace block_quickmail\persistents;

use core\persistent;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_message;
use block_quickmail\persistents\message;
 
class message_draft_recipient extends persistent {
 
    use enhanced_persistent, 
        belongs_to_a_message;

    /** Table name for the persistent. */
    const TABLE = 'block_quickmail_draft_recips';
 
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
            'type' => [
                'type' => PARAM_TEXT, // include, exclude
            ],
            'recipient_type' => [
                'type' => PARAM_TEXT, // role, user, group, filter
            ],
            'recipient_id' => [
                'type' => PARAM_INT,
                'default' => 0
            ],
            'recipient_filter' => [
                'type' => PARAM_TEXT,
                'default' => null,
                'null' => NULL_ALLOWED
            ],
        ];
    }
 
    ///////////////////////////////////////////////
    ///
    ///  GETTERS
    /// 
    ///////////////////////////////////////////////
    
    public function get_recipient_key()
    {
        return $this->get('recipient_type') . '_' . $this->get('recipient_id');
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Deletes all draft recipients for this message
     * 
     * @param  message $message
     * @return void
     */
    public static function clear_all_for_message(message $message)
    {
        global $DB;

        // delete all draft recipients belonging to this message
        $DB->delete_records('block_quickmail_draft_recips', ['message_id' => $message->get('id')]);
    }
 
}