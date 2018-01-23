<?php

namespace block_quickmail\tasks;

use core\task\adhoc_task;
use block_quickmail\persistents\message;
use block_quickmail\persistents\message_recipient;

class send_course_message_to_recipient_adhoc_task extends adhoc_task {
    
    // message_id
    // recipient_id
    public function execute() {
        $message = message::find_or_null($this->message_id);

        $recipient = message_recipient::find_or_null($this->recipient_id);

        self::send_course_message_to_recipient($message, $recipient);
    }
    
}