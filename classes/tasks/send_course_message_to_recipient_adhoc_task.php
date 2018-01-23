<?php

namespace block_quickmail\tasks;

use core\task\adhoc_task;
use block_quickmail\messenger\messenger;
use block_quickmail\persistents\message;
use block_quickmail\persistents\message_recipient;

/**
 * This adhoc task performs the delivery of a message to a single recipient
 */
class send_course_message_to_recipient_adhoc_task extends adhoc_task {
    
    // custom data:
    //  - message_id
    //  - recipient_id
    public function execute() {
        $data = $this->get_custom_data();

        $message = message::find_or_null($data->message_id);

        $recipient = message_recipient::find_or_null($data->recipient_id);

        messenger::send_course_message_to_recipient($message, $recipient, true);
    }
    
}