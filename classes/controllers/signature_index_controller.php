<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_string;
use block_quickmail\persistents\signature;

class signature_index_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/signatures.php';

    public static $views = [
        'signature' => [],
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
            'id' => $this->props->signature_id,
            'courseid' => $this->props->course_id
        ];
    }

    /**
     * Manage user signatures
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function signature(controller_request $request)
    {
        // fetch the requested signature, if any, which must belong to the auth user
        $signature = signature::find_user_signature_or_null($this->props->signature_id, $this->props->user->id);

        $form = $this->make_form('signature_index\signature_form', [
            'context' => $this->context,
            'selected_signature' => $signature,
            'user_signature_array' => signature::get_flat_array_for_user($this->props->user->id)
        ]);

        // list of form submission subactions that may be handled in addition to "back" or "next"
        $subactions = [
            'save',
            'update',
            'delete',
        ];

        // route the form submission, if any
        if ($form->is_submitted_subaction('save', $subactions, true)) {
            return $this->post($request, 'signature', 'save');
        } else if ($form->is_submitted_subaction('update', $subactions, true)) {
            return $this->post($request, 'signature', 'update');
        } else if ($form->is_submitted_subaction('delete', $subactions, true)) {
            return $this->post($request, 'signature', 'delete');
        } else if ($form->is_cancelled()) {
            $request->redirect_to_course_or_my($this->props->course_id);
        }

        $this->render_form($form);
    }

    /**
     * Handles post of signature form, save subaction
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_signature_save(controller_request $request)
    {
        // attempt to create a new signature
        $signature = signature::create_new([
            'user_id' => $this->props->user->id,
            'title' => $request->input->title,
            'signature' => $request->input->signature_editor['text'],
            'default_flag' => property_exists($request->input, 'default_flag') ? $request->input->default_flag : 0,
        ]);

        $request->redirect_as_success(get_string('changessaved'), static::$base_uri, [
            'id' => $signature->get('id'),
            'courseid' => $this->props->course_id
        ]);
    }

    /**
     * Handles post of signature form, update subaction
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_signature_update(controller_request $request)
    {
        // fetch the requested signature, if any, which must belong to the auth user
        $signature = signature::find_user_signature_or_null($request->input->signature_id, $this->props->user->id);

        // update the signature
        $signature->set('title', $request->input->title);
        $signature->set('signature', $request->input->signature_editor['text']);
        $signature->set('default_flag', property_exists($request->input, 'default_flag') ? $request->input->default_flag : 0);
        $signature->update();

        $request->redirect_as_success(get_string('changessaved'), static::$base_uri, [
            'id' => $signature->get('id'),
            'courseid' => $this->props->course_id
        ]);
    }

    /**
     * Handles post of signature form, delete subaction
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_signature_delete(controller_request $request)
    {
        // fetch the requested signature, if any, which must belong to the auth user
        $signature = signature::find_user_signature_or_null($request->input->signature_id, $this->props->user->id);

        // soft delete the signature, flagging a new default if possible
        $signature->soft_delete();

        // get the user's default signature, if any, to redirect back to
        if ( ! $default_signature = signature::get_default_signature_for_user($this->props->user->id)) {
            $redirect_signature_id = 0;
        } else {
            $redirect_signature_id = $default_signature->get('id');
        }

        $request->redirect_as_success(block_quickmail_string::get('user_signature_deleted'), static::$base_uri, [
            'id' => $redirect_signature_id,
            'courseid' => $this->props->course_id
        ]);
    }
    
}