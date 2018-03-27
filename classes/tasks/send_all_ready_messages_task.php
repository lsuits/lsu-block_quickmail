<?php

namespace block_quickmail\tasks;

use core\task\scheduled_task;
use block_quickmail\repos\queued_repo;
use block_quickmail\tasks\send_message_adhoc_task;
use core\task\manager as task_manager;

class send_all_ready_messages_task extends scheduled_task {
    
    public function get_name()
    {
        // Shown in admin screens
        return 'send queued messages'; // convert to lang string
    }

    /*
     * This tasks queries for all messages that should be sent at the current time
     * and initiates sending of each
     * Note: this will in turn kick off subsequent scheduled tasks for each individual message delivery
     *
     * Required custom data: none
     */
    public function execute()
    {
        // get all messages that are queued and ready to send
        $messages = queued_repo::get_all_messages_to_send();

        // iterate through each message
        foreach ($messages as $message) {
            // create a job
            $task = new send_message_adhoc_task();

            $task->set_custom_data([
                'message_id' => $message->get('id')
            ]);

            // queue job
            task_manager::queue_adhoc_task($task);
        }
    }

}