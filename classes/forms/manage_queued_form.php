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

namespace block_quickmail\forms;

require_once $CFG->libdir . '/formslib.php';

use block_quickmail\forms\concerns\is_quickmail_form;

class manage_queued_form extends \moodleform {

    use is_quickmail_form;

    public $errors;
    public $context;
    public $user;
    public $course_id;

    /**
     * Instantiates and returns a queued message management form
     * 
     * @param  object        $context
     * @param  object        $user                   auth user
     * @param  int           $course_id
     * @return \block_quickmail\forms\manage_queued_form
     */
    public static function make($context, $user, $course_id = 0)
    {
        $target_url = self::generate_target_url([
            'courseid' => $course_id,
        ]);

        return new self($target_url, [
            'context' => $context,
            'user' => $user,
            'course_id' => $course_id,
        ], 'post', '', ['id' => 'mform-manage-queued']);
    }

    /*
     * Moodle form definition
     */
    public function definition() {

        $mform =& $this->_form;

        $this->context = $this->_customdata['context'];
        $this->user = $this->_customdata['user'];
        $this->course_id = $this->_customdata['course_id'];

        ////////////////////////////////////////////////////////////
        ///  unqueue (message) id
        ////////////////////////////////////////////////////////////
        $mform->addElement('hidden', 'unqueue_message_id');
        $mform->setType('unqueue_message_id', PARAM_INT);

        ////////////////////////////////////////////////////////////
        ///  send now (message) id
        ////////////////////////////////////////////////////////////
        $mform->addElement('hidden', 'send_now_message_id');
        $mform->setType('send_now_message_id', PARAM_INT);
    }

}
