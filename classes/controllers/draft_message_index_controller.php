<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_string;
use block_quickmail\repos\course_repo;
use block_quickmail\repos\draft_repo;
use block_quickmail\persistents\message;

class draft_message_index_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/drafts.php';

    public static $views = [
        'draft_message_index' => [],
    ];

    public static $actions = [
        'duplicate',
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
        return ['courseid' => $this->props->course_id];
    }

    /**
     * Manage draft messages
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function draft_message_index(controller_request $request)
    {
        // get all (draft) messages belonging to this user and course
        $messages = draft_repo::get_for_user($this->props->user->id, $this->props->course_id, [
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

        $this->render_component('draft_message_index', [
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
     * Delete draft message action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function action_delete(controller_request $request)
    {
        // validate params
        if ( ! $this->props->page_params['message_id']) {
            // unset the action param
            $this->props->page_params['action'] = '';

            // redirect back to index with error
            $request->redirect_as_error(block_quickmail_string::get('message_not_found'), static::$base_uri, $this->props->page_params);
        }

        // attempt to fetch the draft message
        if ( ! $message = draft_repo::find_for_user_or_null($this->props->page_params['message_id'], $this->props->user->id)) {
            // redirect and notify of error
            $request->redirect_as_error(block_quickmail_string::get('draft_no_record'), static::$base_uri, $this->get_form_url_params());
        }

        // attempt to hard delete draft
        $message->hard_delete();

        $request->redirect_as_success(block_quickmail_string::get('message_deleted'), static::$base_uri, $this->get_form_url_params());
    }

    /**
     * Duplicate draft message action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function action_duplicate(controller_request $request)
    {
        // validate params
        if ( ! $this->props->page_params['message_id']) {
            // unset the action param
            $this->props->page_params['action'] = '';

            // redirect back to index with error
            $request->redirect_as_error(block_quickmail_string::get('message_not_found'), static::$base_uri, $this->props->page_params);
        }

        // attempt to fetch the message which must be non-deleted and belong to this user
        if ( ! $message = message::find_owned_by_user_or_null($this->props->page_params['message_id'], $this->props->user->id)) {
            // redirect and notify of error
            $request->redirect_as_error(block_quickmail_string::get('draft_no_record'), static::$base_uri, $this->get_form_url_params());
        }

        // if this message is a draft
        if ($message->get('is_draft')) {
            // attempt to duplicate the draft
            \block_quickmail\messenger\messenger::duplicate_draft($message->get('id'), $this->props->user);
        
        // otherwise, this must be a scheduled, sending, or sent message
        } else {
            // attempt to duplicate the non-draft message
            \block_quickmail\messenger\messenger::duplicate_message($message->get('id'), $this->props->user);
        }

        $request->redirect_as_success(block_quickmail_string::get('redirect_back_to_course_from_message_after_duplicate'), static::$base_uri, $this->get_form_url_params());
    }

}