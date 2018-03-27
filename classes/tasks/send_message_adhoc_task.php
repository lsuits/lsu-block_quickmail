<?php

namespace block_quickmail\tasks;

use core\task\adhoc_task;
use block_quickmail\messenger\messenger;
use block_quickmail\persistents\message;

class send_message_adhoc_task extends adhoc_task {
    
    /*
     * This tasks kicks off the sending of a specific message to all of it's recipients
     * Note: this will in turn kick off subsequent scheduled tasks for each individual recipient delivery
     *
     * Required custom data: message_id
     */
    public function execute() {
        $data = $this->get_custom_data();

        // attempt to fetch the message
        if ($message = message::find_or_null($data->message_id)) {
            // message is found, instantiate a messenger and send
            $messenger = new messenger($message);

            $messenger->handle_message_pre_send();
            $messenger->send();
            $messenger->handle_message_post_send();
        }
    }

}