<?php

namespace block_quickmail\tasks;

use core\task\scheduled_task;

class send_queued_messages_task extends scheduled_task {
    
    public function get_name()
    {
        // Shown in admin screens
        return 'send queued messages'; // convert to lang string
    }

    public function execute()
    {
        // get all messages that are queued and ready to send

        // iterate through each message
            // fire adhoc task send_message_adhoc_task(message_id)
    }

}