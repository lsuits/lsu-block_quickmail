<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_string;
use block_quickmail\persistents\alternate_email;
use block_quickmail\services\alternate_manager;

class alternate_index_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/alternate.php';

    public static $views = [
        'alternate_index' => [],
    ];

    public static $actions = [
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
            $request->redirect_as_error(block_quickmail_string::get('alternate_email_not_found'), static::$base_uri, $this->get_form_url_params());
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
            $request->redirect_as_error(block_quickmail_string::get('alternate_invalid_token'), static::$base_uri, $this->get_form_url_params());
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
            $request->redirect_as_error(block_quickmail_string::get('alternate_email_not_found'), static::$base_uri, $this->get_form_url_params());
        }

        try {
            // attempt to delete the alternate email
            alternate_manager::delete_alternate_email_for_user($this->props->page_params['alternate_id'], $this->props->user);
            
            $request->redirect_as_success(block_quickmail_string::get('alternate_deleted'), static::$base_uri, $this->get_form_url_params());
        } catch (\Exception $e) {
            $request->redirect_as_error($e->getMessage(), static::$base_uri, $this->get_form_url_params());
        }
    }

}