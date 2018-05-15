<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\tasks;

use core\task\scheduled_task;
use block_quickmail\repos\scheduable_repo;
// use block_quickmail\tasks\send_message_adhoc_task;
use core\task\manager as task_manager;

class run_all_ready_scheduled_notifications_task extends scheduled_task {
    
    public function get_name()
    {
        // Shown in admin screens
        return 'run all scheduled notifications'; // convert to lang string
    }

    /*
     * This tasks queries for all scheduables that should be fired at the current time
     * and initiates queueing of each
     *
     * Required custom data: none
     */
    public function execute()
    {
        // WIP!!

        $schedulables = scheduable_repo::get_all_ready_to_run();

        foreach ($schedulables as $schedulable) {
            // $schedulable->run_scheduled();

            // $task = new run_scheduable_adhoc_task();

            // $task->set_custom_data([
            //     'notification_id' => $schedulable->get('notification_id')
            // ]);

            // // queue job
            // task_manager::queue_adhoc_task($task);
        }
    }

}