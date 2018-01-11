<?php

use block_quickmail\persistents\signature;
use block_quickmail\persistents\alternate_email;

class block_quickmail_form {

    /**
     * Instantiates and returns a compose message form
     * 
     * @param  object    $context
     * @param  object    $user           auth user
     * @param  object    $course         moodle course
     * @param  message   $draft_message
     * @return \block_quickmail\forms\compose_message_form
     */
    public static function make_compose_message_form($context, $user, $course, $draft_message = null)
    {
        // build target URL
        $target = '?' . http_build_query([
            'courseid' => $course->id,
            'draftid' => ! empty($draft_message) ? $draft_message->get('id') : 0,
        ], '', '&');

        // get the auth user's available alternate emails for this course
        $user_alternate_email_array = alternate_email::get_flat_array_for_course_user($course->id, $user);

        // get the auth user's current signatures as array (id => title)
        $user_signature_array = signature::get_flat_array_for_user($user->id);

        // get config variables for this course, defaulting to block level
        $course_config_array = block_quickmail_plugin::_c('', $course->id);

        return new \block_quickmail\forms\compose_message_form($target, [
            'context' => $context,
            'user' => $user,
            'course' => $course,
            'user_alternate_email_array' => $user_alternate_email_array,
            'user_signature_array' => $user_signature_array,
            'course_config_array' => $course_config_array,
            'draft_message' => $draft_message,
        ], 'post', '', ['id' => 'mform-compose']);
    }





    /**
     * Instantiates and returns a signature management form
     * 
     * @param  object        $context
     * @param  object        $user                   auth user
     * @param  persistent    $signature              optional, defaults to null
     * @param  int           $course_id              optional, course id
     * @return \block_quickmail\forms\manage_signatures_form
     */
    public static function make_manage_signatures_form($context, $user, $signature = null, $course_id = 0)
    {
        // build target URL
        $target = '?' . http_build_query([
            'id' => ! empty($signature) ? $signature->get('id') : 0,
            'courseid' => $course_id,
        ], '', '&');

        // attempt to fetch the course, return null if not valid
        try {
            $course = get_course($course_id);
        } catch (dml_exception $e) {
            $course = null;
        }

        // get the auth user's current signatures as array (id => title)
        $user_signature_array = signature::get_flat_array_for_user($user->id);

        return new \block_quickmail\forms\manage_signatures_form($target, [
            'context' => $context,
            'user' => $user,
            'signature' => $signature,
            'user_signature_array' => $user_signature_array,
            'course' => $course,
        ], 'post', '', ['id' => 'mform-manage-signatures']);
    }

    /**
     * Instantiates and returns a course configuration management form
     * 
     * @param  object    $context
     * @param  object    $user           auth user
     * @param  object    $course         moodle course
     * @return \block_quickmail\forms\course_config_form
     */
    public static function make_course_config_form($context, $user, $course)
    {
        // build target URL
        $target = '?' . http_build_query([
            'courseid' => $course->id,
        ], '', '&');

        return new \block_quickmail\forms\course_config_form($target, [
            'context' => $context,
            'user' => $user,
            'course' => $course,
        ], 'post', '', ['id' => 'mform-course-config']);
    }

    /**
     * Instantiates and returns a course alternate email management management form
     * 
     * @param  object    $context
     * @param  object    $user           auth user
     * @param  object    $course         moodle course
     * @return \block_quickmail\forms\manage_alternates_form
     */
    public static function make_manage_alternates_form($context, $user, $course)
    {
        // build target URL
        $target = '?' . http_build_query([
            'courseid' => $course->id,
        ], '', '&');

        return new \block_quickmail\forms\manage_alternates_form($target, [
            'context' => $context,
            'user' => $user,
            'course' => $course,
        ], 'post', '', ['id' => 'mform-manage-alternates']);
    }

    /**
     * Instantiates and returns a course message draft management form
     * 
     * @param  object        $context
     * @param  object        $user                   auth user
     * @param  int           $course_id              optional, course id
     * @return \block_quickmail\forms\manage_drafts_form
     */
    public static function make_manage_drafts_form($context, $user, $course_id = 0)
    {
        // build target URL
        $target = '?' . http_build_query([
            'courseid' => $course_id,
        ], '', '&');

        return new \block_quickmail\forms\manage_drafts_form($target, [
            'context' => $context,
            'user' => $user,
            'course_id' => $course_id,
        ], 'post', '', ['id' => 'mform-manage-drafts']);
    }

}