<?php

namespace block_quickmail\tasks;

use core\task\scheduled_task;

class send_unsent_course_messages_task extends scheduled_task {
    
    public function get_name()
    {
        // Shown in admin screens
        return get_string('cutmytoenails', 'mod_hygene');
    }

    public function execute()
    {
        // fetch all messages that belong to courses

        // iterate through each message
        // fire adhoc task send_course_message_task(message_id)
    }

}