<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_string;
use block_quickmail\services\alternate\alternate_manager;

class create_alternate_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/create_alternate.php';

    public static $views = [
        'create_alternate' => [],
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
        return [
            'courseid' => $this->props->course_id,
        ];
    }

    /**
     * Show the create alternate form
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function create_alternate(controller_request $request)
    {
        $form = $this->make_form('create_alternate\create_alternate_form');

        $subactions = ['save'];

        // route the form submission, if any
        if ($form->is_submitted_subaction('save', $subactions, true)) {
            return $this->post($request, 'create_alternate', 'save');
        } else if ($form->is_cancelled()) {
            return $request->redirect_to_url('/blocks/quickmail/alternate.php', ['courseid' => $this->props->course_id]);
        }

        $this->render_form($form);
    }

    /**
     * Handles post of alternate form
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_create_alternate_save(controller_request $request)
    {
        try {
            // attempt to create the alternate and send a confirmation email
            alternate_manager::create_alternate_for_user($this->props->user, $this->props->course_id, [
                'availability' => $request->input->availability,
                'firstname' => $request->input->firstname,
                'lastname' => $request->input->lastname,
                'email' => $request->input->email,
            ]);
        } catch (\Exception $e) {
            $request->redirect_as_error($e->getMessage(), static::$base_uri, $this->get_form_url_params());
        }

        // redirect and notify of success
        $request->redirect_as_success(block_quickmail_string::get('alternate_created'), '/blocks/quickmail/alternate.php', $this->get_form_url_params());
    }

}