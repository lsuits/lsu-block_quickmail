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
use block_quickmail\persistents\concerns\belongs_to_a_notification;
use block_quickmail\persistents\concerns\can_have_a_schedule;
use block_quickmail\persistents\concerns\can_be_soft_deleted;
use block_quickmail\persistents\interfaces\notification_type_interface;
 
class reminder_notification extends persistent implements notification_type_interface {
 
	use enhanced_persistent,
		belongs_to_a_notification,
		can_have_a_schedule,
		can_be_soft_deleted;

	/** Table name for the persistent. */
	const TABLE = 'block_quickmail_rem_notifs';

	/**
	 * Return the definition of the properties of this model.
	 *
	 * @return array
	 */
	protected static function define_properties() {
		return [
			'notification_id' => [
				'type' => PARAM_INT,
			],
			'type' => [
				'type' => PARAM_TEXT,
			],
			'object_id' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
			'begin_at' => [
				'type' => PARAM_INT,
				'default' => null,
				'null' => NULL_ALLOWED,
			],
			'end_at' => [
				'type' => PARAM_INT,
				'default' => null,
				'null' => NULL_ALLOWED,
			],
			'max_per_interval' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
			'schedule_id' => [
				'type' => PARAM_INT,
				'default' => null,
				'null' => NULL_ALLOWED,
			],
			'last_run_at' => [
				'type' => PARAM_INT,
				'default' => null,
				'null' => NULL_ALLOWED,
			],
			'next_run_at' => [
				'type' => PARAM_INT,
				'default' => null,
				'null' => NULL_ALLOWED,
			],
			'timedeleted' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
		];
	}

}