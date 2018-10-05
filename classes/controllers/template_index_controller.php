<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_string;
use block_quickmail\persistents\template;

class template_index_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/templates.php';

    public static $views = [
        'template' => [],
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
            'id' => $this->props->template_id,
        ];
    }

    /**
     * Manage templates
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function template(controller_request $request)
    {
        // fetch the requested template, if any
        $template = template::find_template_or_null($this->props->template_id);

        $form = $this->make_form('template_index\template_form', [
            'context' => $this->context,
            'selected_template' => $template,
            'template_array' => template::get_flat_array()
        ]);

        // list of form submission subactions that may be handled in addition to "back" or "next"
        $subactions = [
            'save',
            'update',
            'delete',
        ];

        // route the form submission, if any
        if ($form->is_submitted_subaction('save', $subactions, true)) {
            return $this->post($request, 'template', 'save');
        } else if ($form->is_submitted_subaction('update', $subactions, true)) {
            return $this->post($request, 'template', 'update');
        } else if ($form->is_submitted_subaction('delete', $subactions, true)) {
            return $this->post($request, 'template', 'delete');
        } else if ($form->is_cancelled()) {
            $request->redirect_to_url('/my');
        }

        $this->render_form($form);
    }

    /**
     * Handles post of template form, save subaction
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_template_save(controller_request $request)
    {
        // attempt to create a new template
        $template = template::create_new([
            'title' => $request->input->title,
            'header_content' => $request->input->header_content,
            'footer_content' => $request->input->footer_content,
            'is_default' => property_exists($request->input, 'is_default') ? $request->input->is_default : 0,
        ]);

        $request->redirect_as_success(get_string('changessaved'), static::$base_uri, [
            'id' => $template->get('id'),
        ]);
    }

    /**
     * Handles post of template form, update subaction
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_template_update(controller_request $request)
    {
        // fetch the requested template, if any
        $template = template::find_template_or_null($request->input->template_id);

        // update the template
        $template->set('title', $request->input->title);
        $template->set('header_content', $request->input->header_content);
        $template->set('footer_content', $request->input->footer_content);
        $template->set('is_default', property_exists($request->input, 'is_default') ? $request->input->is_default : 0);
        $template->update();

        $request->redirect_as_success(get_string('changessaved'), static::$base_uri, [
            'id' => $template->get('id'),
        ]);
    }

    /**
     * Handles post of template form, delete subaction
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_template_delete(controller_request $request)
    {
        // fetch the requested template, if any
        $template = template::find_template_or_null($request->input->template_id);

        // soft delete the template, flagging a new default if possible
        $template->soft_delete();

        // get the default template, if any, to redirect back to
        if ( ! $default_template = template::get_default_template()) {
            $redirect_template_id = 0;
        } else {
            $redirect_template_id = $default_template->get('id');
        }

        $request->redirect_as_success(block_quickmail_string::get('template_deleted'), static::$base_uri, [
            'id' => $redirect_template_id,
        ]);
    }
    
}