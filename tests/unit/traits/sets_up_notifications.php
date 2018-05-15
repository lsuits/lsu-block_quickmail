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
///  NOTIFICATION TEST HELPERS
/// 
////////////////////////////////////////////////////

trait sets_up_notifications {

    //// NOTIFICATION SCAFFOLDING

    public function get_event_notification_params($attr = [], $overrides = [])
    {
        return $this->get_notification_params($attr, $overrides, $this->get_default_event_notification_params());
    }

    public function get_reminder_notification_params($attr = [], $overrides = [])
    {
        return $this->get_notification_params($attr, $overrides, $this->get_default_reminder_notification_params());
    }

    public function get_notification_params($attr = [], $overrides = [], $defaults = [])
    {
        if (is_string($attr)) {
            return $defaults[$attr];
        }

        if (count($overrides)) {
            $defaults = $this->override_params($defaults, $overrides);
        }
        
        if ( ! count($attr)) {
            return $defaults;
        }

        // get rid of non-course-configurable fields
        return array_filter($defaults, function ($key) use ($attr) {
            return in_array($key, $attr);
        }, ARRAY_FILTER_USE_KEY);
    }

    public function get_default_event_notification_params()
    {
        return array_merge($this->get_default_notification_params(), [
            'name' => 'My Event Notification',
            'time_delay' => 0,
        ]);
    }

    public function get_default_reminder_notification_params()
    {
        $now = time();

        return array_merge($this->get_default_notification_params(), [
            'name' => 'My Reminder Notification',
            'schedule_unit' => 'week',
            'schedule_amount' => 1,
            'schedule_begin_at' => $now,
            'schedule_end_at' => null,
            'max_per_interval' => 0,
        ]);
    }

    public function get_default_notification_params()
    {
        return [
            'message_type' => 'email',
            'subject' => 'This is the subject',
            'body' => 'This is the body',
            'is_enabled' => 1,
            'conditions' => '',
            'alternate_email_id' => 0,
            'signature_id' => 0,
            'editor_format' => 1,
            'send_receipt' => 0,
            'send_to_mentors' => 0,
            'no_reply' => 1,
        ];
    }

}