<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_config;

class course_config_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/configuration.php';

    public static $views = [
        'course_config' => [],
    ];

    /**
     * Returns the query string which this controller's forms will append to target URLs
     *
     * NOTE: this overrides the base controller method
     * 
     * @return array
     */
    public function get_form_url_params()
    {
        return ['courseid' => $this->props->course->id];
    }

    /**
     * Manage course configuration
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function course_config(controller_request $request)
    {
        $form = $this->make_form('course_config\course_config_form', [
            'course_config' => block_quickmail_config::get('', $this->props->course),
            'context' => $this->context,
            'user' => $this->props->user,
            'user_preferred_picker' => get_user_preferences('block_quickmail_preferred_picker', 'autocomplete', $this->props->user)
        ]);

        // list of form submission subactions that may be handled in addition to "back" or "next"
        $subactions = [
            'reset',
            'save',
        ];

        // route the form submission, if any
        if ($form->is_submitted_subaction('reset', $subactions)) {
            return $this->post($request, 'course_config', 'reset');
        } else if ($form->is_submitted_subaction('save', $subactions)) {
            return $this->post($request, 'course_config', 'save');
        } else if ($form->is_cancelled()) {
            $request->redirect_to_url('/course/view.php', ['id' => $this->props->course->id]);
        }

        $this->render_form($form);
    }

    /**
     * Handles post of course_config form, reset subaction
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_course_config_reset(controller_request $request)
    {
        // delete this course's config settings
        block_quickmail_config::delete_course_config($this->props->course);

        $request->redirect_as_success(get_string('changessaved'), '/blocks/quickmail/configuration.php', ['courseid' => $this->props->course->id]);
    }

    /**
     * Handles post of course_config form, save subaction
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_course_config_save(controller_request $request)
    {
        block_quickmail_config::update_course_config($this->props->course, (array) $request->input);

        $this->set_user_preferred_picker($request->input);

        $request->redirect_as_success(get_string('changessaved'), '/blocks/quickmail/configuration.php', ['courseid' => $this->props->course->id]);
    }

    /**
     * Sets the user's personally preferred picker option based on input
     * 
     * @param stdClass  $input
     */
    private function set_user_preferred_picker($input)
    {
        // sanitize input option, defaulting to autocomplete
        if ( ! property_exists($input, 'preferred_picker')) {
            $preferred_picker = 'autocomplete';
        } else if ( ! in_array($input->preferred_picker, ['autocomplete', 'multiselect'])) {
            $preferred_picker = 'autocomplete';
        } else {
            $preferred_picker = $input->preferred_picker;
        }

        set_user_preference('block_quickmail_preferred_picker', $preferred_picker, $this->props->user);
    }
    
}