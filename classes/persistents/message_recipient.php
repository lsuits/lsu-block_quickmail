<?php

namespace block_quickmail\persistents;

use core\persistent;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_message;
use block_quickmail\persistents\concerns\belongs_to_a_user;
use block_quickmail\persistents\message;
 
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
            'moodle_message_id' => [
                'type' => PARAM_INT,
                'default' => 0
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

    /**
     * Returns this message recipient's parent message
     * 
     * @return message
     */
    public function get_message()
    {
        $message_id = $this->get('message_id');

        return new message($message_id);
    }

    ///////////////////////////////////////////////
    ///
    ///  SETTERS
    /// 
    ///////////////////////////////////////////////
    
    //

    ///////////////////////////////////////////////
    ///
    ///  HELPER METHODS
    /// 
    ///////////////////////////////////////////////
    
    /**
     * Reports whether or not this recipient should be the first to receive this message
     * 
     * @return bool
     */
    public function should_be_first_to_receive_message()
    {
        // get all unsent recipients of this message
        $recipients = $this->get_all_recipients_for_message(true);

        if ( ! count($recipients)) {
            return false;
        }

        $first = $recipients[0]->get('id');

        unset($recipients);

        return $first == $this->get('id');
    }

    /**
     * Reports whether or not this recipient should be the last to receive this message
     * 
     * @return bool
     */
    public function should_be_last_to_receive_message()
    {
        // get all unsent recipients of this message
        $recipients = $this->get_all_recipients_for_message(true);

        if ( ! count($recipients)) {
            return false;
        }

        $reversed = array_reverse($recipients);

        $last = $reversed[0]->get('id');

        unset($recipients);
        unset($reversed);

        return $last == $this->get('id');
    }

    /**
     * Return an array of all recipients of this recipient's parent message
     * 
     * @param  bool  $unsent_only
     * @return array
     */
    public function get_all_recipients_for_message($unsent_only = false)
    {
        // get parent message
        $message = $this->get_message();

        $params = ['message_id' => $message->get('id')];

        if ($unsent_only) {
            $params['sent_at'] = 0;
        }

        // get all recipients of this message
        $recipients = self::get_records($params);

        return $recipients;
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Deletes all recipients for this message
     * 
     * @param  message $message
     * @return void
     */
    public static function clear_all_for_message(message $message)
    {
        global $DB;

        // delete all recipients belonging to this message
        $DB->delete_records('block_quickmail_msg_recips', ['message_id' => $message->get('id')]);
    }

    /**
     * Update the recipient belonging to the given message and user as have been sent to right now
     * 
     * @param  message    $message
     * @param  core_user  $user
     * @param  int        $moodle_message_id
     * @return void
     */
    public static function mark_as_sent(message $message, $user, $moodle_message_id = 0)
    {
        $recipient = self::get_record([
            'message_id' => $message->get('id'), 
            'user_id' => $user->id
        ]);

        $recipient->set('sent_at', time());
        $recipient->set('moodle_message_id', (int) $moodle_message_id);
        
        $recipient->update();
    }
 
}