<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_string;
use block_quickmail\persistents\alternate_email;
use block_quickmail\services\alternate\alternate_manager;

class alternate_index_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/alternate.php';

    public static $views = [
        'alternate_index' => [],
    ];

    public static $actions = [
        'create',
        'resend',
        'confirm',
        'delete',
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
     * Manage user alternate emails
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function alternate_index(controller_request $request)
    {
        // get all alternate emails belonging to this user
        $alternates = alternate_email::get_all_for_user($this->props->user->id);

        $this->render_component('alternate_index', [
            'alternates' => $alternates,
            'course_id' => $this->props->course_id,
        ]);
    }

    /**
     * Show the create alternate form
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function action_create(controller_request $request)
    {
        $form = $this->make_form('alternate_index\create_alternate_form', [], 'create');

        // list of form submission subactions that may be handled in addition to "back" or "next"
        $subactions = [
            'save',
        ];

        // route the form submission, if any
        if ($form->is_submitted_subaction('save', $subactions, true)) {
            return $this->post($request, 'alternate', 'save');
        } else if ($form->is_cancelled()) {
            $request->redirect_to_course_or_my($this->props->course_id);
        }

        $this->render_form($form);
    }

    /**
     * Handles post of alternate form, save subaction
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_alternate_save(controller_request $request)
    {
        // attempt to create the alternate and send a confirmation email
        alternate_manager::create_alternate_for_user($this->props->user, $this->props->page_params['course_id'], [
            'availability' => $request->input->availability,
            'firstname' => $request->input->firstname,
            'lastname' => $request->input->lastname,
            'email' => $request->input->email,
        ]);

        // redirect and notify of success
        $request->redirect_as_success(block_quickmail_string::get('alternate_created'), static::$base_uri, $this->get_form_url_params());
    }

    /**
     * Resend alternate email confirmation email action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function action_resend(controller_request $request)
    {
        // validate params
        if ( ! $this->props->page_params['alternate_id']) {
            // redirect back to index with error
            $request->redirect_as_error('No alternate was specified!', static::$base_uri, $this->get_form_url_params());
        }

        try {
            // attempt to resend the alternate email confirmation email
            alternate_manager::resend_confirmation_email_for_user($this->props->page_params['alternate_id'], $this->props->user);
            
            $request->redirect_as_success(block_quickmail_string::get('alternate_confirmation_email_resent'), static::$base_uri, $this->get_form_url_params());
        } catch (\Exception $e) {
            $request->redirect_as_error($e->getMessage(), static::$base_uri, $this->get_form_url_params());
        }
    }

    /**
     * Confirm alternate email action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function action_confirm(controller_request $request)
    {
        // validate params
        if ( ! $this->props->page_params['alternate_id'] || ! $this->props->page_params['token']) {
            // redirect back to index with error
            $request->redirect_as_error('Invalid confirmation token!', static::$base_uri, $this->get_form_url_params());
        }

        try {
            // attempt to confirm the alternate email
            $alternate_email = alternate_manager::confirm_alternate_for_user(
                $this->props->page_params['alternate_id'],
                $this->props->page_params['token'],
                $this->props->user
            );
            
            $request->redirect_as_success(block_quickmail_string::get('alternate_activated', $alternate_email->get('email')), static::$base_uri, $this->get_form_url_params());
        } catch (\Exception $e) {
            $request->redirect_as_error($e->getMessage(), static::$base_uri, $this->get_form_url_params());
        }
    }

    /**
     * Delete alternate email action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function action_delete(controller_request $request)
    {
        // validate params
        if ( ! $this->props->page_params['alternate_id']) {
            // redirect back to index with error
            $request->redirect_as_error('No alternate was specified!', static::$base_uri, $this->get_form_url_params());
        }

        try {
            // attempt to delete the alternate email
            alternate_manager::delete_alternate_email_for_user($this->props->page_params['alternate_id'], $this->props->user);
            
            $request->redirect_as_success('Alternate deleted!', static::$base_uri, $this->get_form_url_params());
        } catch (\Exception $e) {
            $request->redirect_as_error($e->getMessage(), static::$base_uri, $this->get_form_url_params());
        }
    }

}