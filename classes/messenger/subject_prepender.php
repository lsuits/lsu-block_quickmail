<?php

namespace block_quickmail\messenger;

use block_quickmail_config;
use block_quickmail_string;

class subject_prepender {

    public $subject;

    /**
     * Construct the message subject prepender
     * 
     * @param string  $subject   the message subject
     */
    public function __construct($subject) {
        $this->subject = $subject;
    }

    /**
     * Returns a formatted subject line for the given course and raw subject,
     * decorating with any prependages if necessary
     * 
     * @param  object  $course
     * @param  string  $subject
     * @return string
     */
    public static function format_course_subject($course, $subject)
    {
        $prepender = new self($subject);

        return $prepender->get_course_formatted($course);
    }

    /**
     * Returns a subject line formatted for a send receipt email
     * 
     * @param  string $subject
     * @return string
     */
    public static function format_for_receipt_subject($subject)
    {
        return block_quickmail_string::get('send_receipt_subject_addendage') . ': ' . $subject;
    }

    /**
     * Returns a subject prependage for the given course based on configuration
     * 
     * @param  object  $course
     * @return string
     */
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

    /**
     * Returns the given course's "prepend class" setting
     * 
     * @param  object $course
     * @return string
     */
    private function get_course_config_setting($course)
    {
        return block_quickmail_config::get('prepend_class', $course);
    }

    /**
     * Returns the subject string prepended with course appendage string
     * 
     * @param  string $value
     * @return string
     */
    private function get_prepended_with($value)
    {
        return $this->get_left_delimiter() . $value . $this->get_right_delimiter() . ' ' . $this->subject;
    }

    /**
     * Returns the delimiter to be rendered on the left side of the course appendage
     * 
     * @return string
     */
    private function get_left_delimiter()
    {
        return '[';
    }

    /**
     * Returns the delimiter to be rendered on the right side of the course appendage
     * 
     * @return string
     */
    private function get_right_delimiter()
    {
        return ']';
    }

}