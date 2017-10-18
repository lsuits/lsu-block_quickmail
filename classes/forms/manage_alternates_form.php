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
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\forms;

require_once $CFG->libdir . '/formslib.php';

class manage_alternates_form extends \moodleform {

    public $context;

    public $user;

    public $course;

    public function definition() {

        $mform = $this->_form;

        // set the context
        $this->context = $this->_customdata['context'];
        
        // set the user
        $this->user = $this->_customdata['user'];

        // set the course
        $this->course = $this->_customdata['course'];

        // delete id
        $mform->addElement('hidden', 'delete_alternate_id');
        $mform->setType('delete_alternate_id', PARAM_INT);
        // $mform->setDefault('delete_signature_flag', 0);
        
        // create flag
        $mform->addElement('hidden', 'create_flag');
        $mform->setType('create_flag', PARAM_INT);
        $mform->setDefault('create_flag', 0);

        // firstname
        $mform->addElement('hidden', 'firstname');
        $mform->setType('firstname', PARAM_TEXT);
        $mform->setDefault('firstname', '');

        // lastname
        $mform->addElement('hidden', 'lastname');
        $mform->setType('lastname', PARAM_TEXT);
        $mform->setDefault('lastname', '');

        // email
        $mform->addElement('hidden', 'email');
        $mform->setType('email', PARAM_TEXT);
        $mform->setDefault('email', '');

        // availability
        $mform->addElement('hidden', 'availability');
        $mform->setType('availability', PARAM_TEXT);
        $mform->setDefault('availability', '');
    }

}
