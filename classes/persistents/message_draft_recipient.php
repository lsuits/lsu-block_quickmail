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

// use \core\persistent;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_message;
use block_quickmail\persistents\message;
 
// if ( ! class_exists('\core\persistent')) {
//     class_alias('\block_quickmail\persistents\persistent', '\core\persistent');
// }

class message_draft_recipient extends \block_quickmail\persistents\persistent {
 
    use enhanced_persistent, 
        belongs_to_a_message;

    /** Table name for the persistent. */
    const TABLE = 'block_quickmail_draft_recips';
 
    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'message_id' => [
                'type' => PARAM_INT,
            ],
            'type' => [
                'type' => PARAM_TEXT, // include, exclude
            ],
            'recipient_type' => [
                'type' => PARAM_TEXT, // role, user, group, filter
            ],
            'recipient_id' => [
                'type' => PARAM_INT,
                'default' => 0
            ],
            'recipient_filter' => [
                'type' => PARAM_TEXT,
                'default' => null,
                'null' => NULL_ALLOWED
            ],
        ];
    }
 
    ///////////////////////////////////////////////
    ///
    ///  GETTERS
    /// 
    ///////////////////////////////////////////////
    
    public function get_recipient_key()
    {
        return $this->get('recipient_type') . '_' . $this->get('recipient_id');
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Deletes all draft recipients for this message
     * 
     * @param  message $message
     * @return void
     */
    public static function clear_all_for_message(message $message)
    {
        global $DB;

        // delete all draft recipients belonging to this message
        $DB->delete_records('block_quickmail_draft_recips', ['message_id' => $message->get('id')]);
    }
 
}