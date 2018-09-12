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

namespace block_quickmail\persistents\concerns;

use block_quickmail\persistents\notification;
use block_quickmail\notifier\models\notification_model;
use block_quickmail_string;
use block_quickmail\notifier\notification_condition;

trait is_notification_type {

    ///////////////////////////////////////////////
    ///
    ///  RELATIONSHIPS
    /// 
    ///////////////////////////////////////////////

    /**
     * Returns the parent notification object of this notification type interface
     *
     * @return stdClass
     */
    public function get_notification() {
        return notification::find_or_null($this->get('notification_id'));
    }

    ///////////////////////////////////////////////
    ///
    ///  NOTIFICATION MODELS
    /// 
    ///////////////////////////////////////////////

    /**
     * Returns the notification_model implementation for this notification_type_interface
     * 
     * @return notification_model_interface
     */
    public function get_notification_model()
    {
        return notification_model::make($this);
    }

    ///////////////////////////////////////////////
    ///
    ///  GETTERS
    /// 
    ///////////////////////////////////////////////
    
    /**
     * Reports whether or not this notification type is schedulable
     * 
     * @return bool
     */
    public function is_schedulable()
    {
        return in_array('block_quickmail\persistents\interfaces\schedulable_interface', class_implements($this));
    }

    /**
     * Returns a human readable title of this notification type
     * 
     * @return string
     */
    public function get_title()
    {
        return block_quickmail_string::get('notification_model_' . static::$notification_type_key . '_' . str_replace('-', '_', $this->get('model')));
    }

    /**
     * Returns a human readable description of this notification type
     * 
     * @return string
     */
    public function get_description()
    {
        return block_quickmail_string::get('notification_model_' . static::$notification_type_key . '_' . str_replace('-', '_', $this->get('model')) . '_description');
    }

    /**
     * Returns a human readable description of this notification type's condition description
     * 
     * @return string
     */
    public function get_condition_description()
    {
        return block_quickmail_string::get('notification_model_' . static::$notification_type_key . '_' . str_replace('-', '_', $this->get('model')) . '_condition_description');
    }

}