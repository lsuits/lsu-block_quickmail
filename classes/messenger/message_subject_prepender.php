<?php

namespace block_quickmail\messenger;

use block_quickmail_config;

class message_subject_prepender {

    public $subject;

    /**
     * Construct the message subject prepender
     * 
     * @param string  $subject   the message subject
     */
    public function __construct($subject) {
        $this->subject = $subject;
    }

    public static function format_course_subject($course, $subject)
    {
        $prepender = new self($subject);

        return $prepender->get_course_formatted($course);
    }

    public function get_course_formatted($course)
    {
        // get course config
        $setting = $this->get_course_config_setting($course);

        switch ($setting) {
            case 'idnumber':
                return $this->get_prepended_with($course->idnumber);
                break;

            case 'shortname':
                return $this->get_prepended_with($course->shortname);
                break;

            case 'fullname':
                return $this->get_prepended_with($course->fullname);
                break;
            
            default:
                return $this->subject;
                break;
        }
    }

    private function get_course_config_setting($course)
    {
        return block_quickmail_config::_c('prepend_class', $course);
    }

    private function get_prepended_with($value)
    {
        return $this->get_left_delimiter() . $value . $this->get_right_delimiter() . ' ' . $this->subject;
    }

    private function get_left_delimiter()
    {
        return '[';
    }

    private function get_right_delimiter()
    {
        return ']';
    }

}