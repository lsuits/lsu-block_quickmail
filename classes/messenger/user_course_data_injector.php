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

namespace block_quickmail\messenger;

use block_quickmail\messenger\body_parser;
use block_quickmail_config;

class user_course_data_injector {

    public $user;
    public $course;
    public $message_body;
    public $parser;
    public $message_keys;

    /**
     * Construct the user_course_data_injector
     * 
     * @param object  $user          the moodle user
     * @param object  $course        the moodle course
     * @param string  $message_body  the initial message body to be formatted
     */
    public function __construct($user, $course, $message_body) {
        $this->user = $user;
        $this->course = $course;
        $this->message_body = $message_body;
        $this->parser = new body_parser($this->message_body);
        $this->keys = $this->parser->get_keys_from_message_body();

    }

    public static function get_message_body($user, $course, $message_body)
    {
        $injector = new self($user, $course, $message_body);

        $injector->inject_message_data();

        return $injector->message_body;
    }

    public function inject_message_data()
    {
        // if no data to inject, skip
        if (empty($this->keys)) {
            return;
        }

        $supported_keys = block_quickmail_config::get_supported_data_injection_fields();

        foreach ($this->keys as $key) {
            if ( ! in_array($key, $supported_keys)) {
                continue;
            }

            $key_stamp = body_parser::get_delimited_key_stamp($key);

            $this->message_body = str_replace($key_stamp, $this->get_mapped_data($key), $this->message_body);
        }
    }

    public function get_mapped_data($key)
    {
        $method = 'get_data_' . $key;

        return $this->$method();
    }

    public function get_data_firstname()
    {
        return $this->user->firstname;
    }

    public function get_data_middlename()
    {
        return $this->user->middlename;
    }

    public function get_data_lastname()
    {
        return $this->user->lastname;
    }

    public function get_data_email()
    {
        return $this->user->email;
    }

    public function get_data_alternatename()
    {
        return $this->user->alternatename;
    }

    public function get_data_coursefullname()
    {
        return $this->course->fullname;
    }

    public function get_data_courseshortname()
    {
        return $this->course->shortname;
    }

    public function get_data_courseidnumber()
    {
        return $this->course->idnumber;
    }

    public function get_data_coursesummary()
    {
        return $this->course->summary;
    }

    public function get_data_coursestartdate()
    {
        return date('F j, Y', $this->course->startdate);
    }

    public function get_data_courseenddate()
    {
        return date('F j, Y', $this->course->enddate);
    }

}