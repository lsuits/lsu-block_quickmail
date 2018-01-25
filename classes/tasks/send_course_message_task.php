<?php

namespace block_quickmail\tasks;

use core\task\adhoc_task;
use block_quickmail\messenger\messenger;
use block_quickmail\persistents\message;

class send_course_message_task extends adhoc_task {
    
    // custom data:
    //  - message_id
    public function execute() {
        $data = $this->get_custom_data();

        $message = message::find_or_null($data->message_id);

        $messenger = new messenger($message);

        $messenger->send();
    }

}