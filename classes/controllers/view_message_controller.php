<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;

class view_message_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/message.php';

    public static $views = [
        'view_message' => [],
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
        return ['id' => $this->props->message->get('id')];
    }

    /**
     * View a queued, sent, or sending message's details
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function view_message(controller_request $request)
    {
        // get message recipients as array of user objects
        $recipient_users = $this->props->message->get_message_recipient_users('email,firstname,lastname');

        // get message additional emails as array
        $additional_emails = $this->props->message->get_additional_emails(true);

        // get message file attachments
        $attachments = $this->props->message->get_message_attachments();

        $this->render_component('view_message', [
            'message' => $this->props->message,
            'user' => $this->props->user,
            'recipient_users' => $recipient_users,
            'additional_emails' => $additional_emails,
            'attachments' => $attachments
        ]);
    }

}