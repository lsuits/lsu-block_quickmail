<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_string;
use block_quickmail\repos\course_repo;
use block_quickmail\repos\queued_repo;
use block_quickmail\persistents\message;

class queued_message_index_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/queued.php';

    public static $views = [
        'queued_message_index' => [],
    ];

    public static $actions = [
        'send',
        'unqueue'
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
        return ['courseid' => $this->props->course_id];
    }

    /**
     * Manage queued messages
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function queued_message_index(controller_request $request)
    {
        // get all (queued) messages belonging to this user and course
        $messages = queued_repo::get_for_user($this->props->user->id, $this->props->course_id, [
            'sort' => $this->props->page_params['sort'], 
            'dir' => $this->props->page_params['dir'],
            'paginate' => true,
            'page' => $this->props->page_params['page'], 
            'per_page' => $this->props->page_params['per_page'],
            'uri' => $_SERVER['REQUEST_URI']
        ]);

        // filter out messages not in this course
        $filtered_messages = message::filter_messages_by_course($messages->data, $this->props->course_id);

        // get this user's courses
        $user_course_array = course_repo::get_user_course_array($this->props->user);

        $this->render_component('queued_message_index', [
            'messages' => $filtered_messages,
            'user_course_array' => $user_course_array,
            'course_id' => $this->props->course_id,
            'user' => $this->props->user,
            'pagination' => $messages->pagination,
            'sort_by' => $this->props->page_params['sort'],
            'sort_dir' => $this->props->page_params['dir'],
        ]);
    }

    /**
     * Unqueue message action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function action_unqueue(controller_request $request)
    {
        // validate params
        if ( ! $this->props->page_params['message_id']) {
            // unset the action param
            $this->props->page_params['action'] = '';

            // redirect back to index with error
            $request->redirect_as_error(block_quickmail_string::get('message_not_found'), static::$base_uri, $this->props->page_params);
        }

        // attempt to fetch the message to unqueue
        if ( ! $message = queued_repo::find_for_user_or_null($this->props->page_params['message_id'], $this->props->user->id)) {
            // redirect and notify of error
            $request->redirect_as_error(block_quickmail_string::get('queued_no_record'), static::$base_uri, $this->get_form_url_params());
        }

        // attempt to unqueue
        $message->unqueue();

        $request->redirect_as_success(block_quickmail_string::get('message_unqueued'), static::$base_uri, $this->get_form_url_params());
    }

    /**
     * Send message action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function action_send(controller_request $request)
    {
        // validate params
        if ( ! $this->props->page_params['message_id']) {
            // unset the action param
            $this->props->page_params['action'] = '';

            // redirect back to index with error
            $request->redirect_as_error(block_quickmail_string::get('message_not_found'), static::$base_uri, $this->props->page_params);
        }

        // attempt to fetch the message to send now
        if ( ! $message = queued_repo::find_for_user_or_null($this->props->page_params['message_id'], $this->props->user->id)) {
            // redirect and notify of error
            $request->redirect_as_error(block_quickmail_string::get('queued_no_record'), static::$base_uri, $this->get_form_url_params());
        }

        // attempt to force the queued message to be sent now
        $message->send();

        $request->redirect_as_success(block_quickmail_string::get('message_sent_now'), static::$base_uri, $this->get_form_url_params());
    }

}