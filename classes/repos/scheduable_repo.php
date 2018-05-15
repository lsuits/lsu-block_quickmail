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

namespace block_quickmail\repos;

use block_quickmail\repos\repo;
use block_quickmail\repos\interfaces\scheduable_repo_interface;
// use block_quickmail\persistents\message;

class scheduable_repo extends repo implements scheduable_repo_interface {

	public $default_sort = 'created';

	public $default_dir = 'desc';
	
	public $sortable_attrs = [
		'id' => 'id',
		'created' => 'timecreated',
		'scheduled' => 'next_run_at',
	];

	/**
	 * Returns an array of all scheduables that should be sent by the system right now
	 * 
	 * @return array
	 */
	public static function get_all_ready_to_run()
	{
		// WIP!!

		// global $DB;

		// $now = \DateTime::createFromFormat('U', time(), \core_date::get_server_timezone_object());
  //       $now = $now->getTimestamp();

  //       return $now;

		// $sql = 'SELECT n.* 
		// 		FROM {block_quickmail_notifs} n
		// 		WHERE m.is_draft = 0 
		// 		AND m.is_sending = 0 
		// 		AND m.timedeleted = 0 
		// 		AND m.to_send_at <= :now AND m.sent_at = 0';

		// // pull data, iterate through recordset, instantiate persistents, add to array
		// $data = [];
		// $recordset = $DB->get_recordset_sql($sql, ['now' => $now]);
		// foreach ($recordset as $record) {
		// 	$data[] = new message(0, $record);
		// }
		// $recordset->close();

		// return $data;
	}

}