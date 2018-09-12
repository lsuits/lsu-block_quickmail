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

////////////////////////////////////////////////////
///
///  NOTIFICATION MODEL TEST HELPERS
/// 
////////////////////////////////////////////////////

use block_quickmail\persistents\reminder_notification;

trait sets_up_notification_models {

    public function create_reminder_notification_model($model_key, $course, $creating_user, $object, $override_params = [])
    {
        $model_class_name = str_replace('-', '_', $model_key) . '_model';

        // create test reminder notification
        $reminder_notification = reminder_notification::create_type($model_key, $course, $object, $creating_user, $this->get_reminder_notification_params([], $override_params));

        return $this->create_notification_model('reminder', $model_class_name, $reminder_notification);
    }

    public function create_notification_model($type, $model_class_name, $notification_type_interface)
    {
        $class = 'block_quickmail\notifier\models\\' . $type . '\\' . $model_class_name;

        return new $class($notification_type_interface, $notification_type_interface->get_notification());
    }

}