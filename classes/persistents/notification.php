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

namespace block_quickmail\persistents;

use \core\persistent;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_course;
use block_quickmail\persistents\concerns\belongs_to_a_user;
use block_quickmail\persistents\concerns\can_be_soft_deleted;
use block_quickmail\persistents\event_notification;
use block_quickmail\persistents\reminder_notification;

class notification extends persistent {
 
	use enhanced_persistent,
		belongs_to_a_course,
		belongs_to_a_user,
		can_be_soft_deleted;

	/** Table name for the persistent. */
	const TABLE = 'block_quickmail_notifs';

	/**
	 * Return the definition of the properties of this model.
	 *
	 * @return array
	 */
	protected static function define_properties() {
		return [
			'name' => [
				'type' => PARAM_TEXT,
			],
			'type' => [
				'type' => PARAM_TEXT,
			],
			'course_id' => [
				'type' => PARAM_INT,
			],
			'user_id' => [
				'type' => PARAM_INT,
			],
			'is_enabled' => [
				'type' => PARAM_BOOL,
				'default' => false,
			],
			'conditions' => [
				'type' => PARAM_TEXT,
				'default' => null,
				'null' => NULL_ALLOWED,
			],
			'message_type' => [
				'type' => PARAM_TEXT,
			],
			'alternate_email_id' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
			'subject' => [
				'type' => PARAM_TEXT,
				'default' => null,
				'null' => NULL_ALLOWED,
			],
			'signature_id' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
			'body' => [
				'type' => PARAM_RAW,
				'default' => null,
				'null' => NULL_ALLOWED,
			],
			'editor_format' => [
				'type' => PARAM_INT,
				'default' => 1, // @TODO - make this configurable?
			],
			'send_receipt' => [
				'type' => PARAM_BOOL,
				'default' => false,
			],
			'send_to_mentors' => [
				'type' => PARAM_BOOL,
				'default' => false,
			],
			'no_reply' => [
				'type' => PARAM_BOOL,
				'default' => false,
			],
			'timedeleted' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
		];
	}

	///////////////////////////////////////////////
	///
	///  RELATIONSHIPS
	/// 
	///////////////////////////////////////////////

	/**
	 * Returns the notification type interface instance of this notification
	 *
	 * Based off of "type" property, defaults to null (should not happen)
	 * 
	 * @return \block_quickmail\persistents\interfaces\notification_type_interface (event/reminder)
	 */
	public function get_notification_type_interface()
	{
		if ($this->get('type') == 'event') {
			return event_notification::find_or_null($this->get('id'));
		} else if ($this->get('type') == 'reminder') {
			return reminder_notification::find_or_null($this->get('id'));
		} else {
			return null;
		}
	}

	///////////////////////////////////////////////
	///
	///  GETTERS
	/// 
	///////////////////////////////////////////////
	
	/**
	 * Reports whether or not this notification is enabled
	 * 
	 * @return bool
	 */
	public function is_notification_enabled()
	{
		return (bool) $this->get('is_enabled');
	}

}