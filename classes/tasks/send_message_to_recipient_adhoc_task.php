<?php

namespace block_quickmail\tasks;

use core\task\adhoc_task;
use block_quickmail\messenger\messenger;
use block_quickmail\persistents\message;
use block_quickmail\persistents\message_recipient;

class send_message_to_recipient_adhoc_task extends adhoc_task {
    
    /*
     * This task kicks off the sending of a message to one child recipient
     * Note: this will in turn kick off subsequent scheduled tasks for each individual recipient delivery
     *
     * Required custom data: message_id, recipient_id
     */
    public function execute() {
        $data = $this->get_custom_data();

        // attempt to fetch the parent message
        if ($message = message::find_or_null($data->message_id)) {
            // attempt to fetch the single recipient
            if ($recipient = message_recipient::find_or_null($data->recipient_id)) {
                // send only if the recipient has not been sent to
                if ( ! $recipient->has_been_sent_to()) {
                    $messenger = new messenger($message);
                        
                    $messenger->send_to_recipient($recipient, true);
                }
            }
        }
    }
    
}