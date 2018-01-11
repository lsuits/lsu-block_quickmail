<?php

namespace block_quickmail\messenger;

use block_quickmail\persistents\message;
use block_quickmail\validators\compose_message_form_validator;
use block_quickmail\requests\compose as compose_request;
use block_quickmail\exceptions\validation_exception;
use block_quickmail\exceptions\critical_exception;

class messenger {

    public function __construct()
    {
        //
    }

    // alternate_email_id
    // mailto_ids
    // subject
    // additional_emails
    // message
    // attachments
    // signature_id
    // output_channel
    // receipt

    public static function send_composed_course_message($user, $course, $form_data, $draft_message = null)
    {
        // validate form data
        $validator = new compose_message_form_validator($form_data, [
            'course_config' => \block_quickmail_plugin::_c('', $course->id)
        ]);

        if ($validator->has_errors()) {
            throw new validation_exception('Validation exception!', $validator->errors);
        }

        // if draft message is passed,
        if ( ! empty($draft_message)) {
            // if draft message was already sent (shouldn't happen)
            if ($draft_message->is_sent_message()) {
                throw new validation_exception('Critical exception!');
            }

            // update draft message record with form data
            $message = self::update_draft_message_with_course_compose_post($draft_message, $form_data);
        } else {
            // create a new message from form data
            $message = self::create_message_from_course_compose_post($form_data, $user, $course);
        }
        
        // @TODO: handle posted file attachments (moodle)

        // clear any existing recipients, and add those that have been recently submitted
        $message->sync_recipients(compose_request::get_transformed_mailto_ids($form_data));

        // clear any existing additional emails, and add those that have been recently submitted
        $message->sync_additional_emails(compose_request::get_transformed_additional_emails($form_data));

        // @TODO: sync posted attachments to message record
        
        // if sending immediately, (for right now lets send immediately)
            self::execute_course_message($message, false);
        
        // if no exceptions, send positive response
        return true;
    }

    public static function execute_course_message(message $message, $queue_send = false)
    {
        // is message is currently being sent, bail out
        if ($message->is_being_sent()) {
            return false;
        }

        // if sending now, handle pre-send actions
        if ( ! $queue_send) {
            self::handle_message_pre_send($message);
        }

        // iterate through all message recipients
        foreach($message->get_message_recipients() as $recipient) {
            if ( ! $queue_send) {
                self::send_course_message_to_recipient($message, $recipient, false);
            } else {
                var_dump('here!!');die;
                // fire adhoc task send_course_message_to_recipient_task(message_id, recipient_id)
            }
        }
        
        // if sending now, handle post-send actions
        if ( ! $queue_send) {
            self::handle_message_post_send($message);
        }

        var_dump('star');die;
    }

    public static function send_course_message_to_recipient($message, $recipient, $event_handling = false)
    {
        // if we're handling pre/post send actions (likely, is queued send) AND this recipient should be first to receive message
        if ($event_handling && $recipient->should_be_first_to_receive_message()) {
            self::handle_message_pre_send($message);
        }

        // $send_factory = new send_factory($message, $recipient);

        // instantiate course_send_factory
            // message
            // recipient

        // send course_send_factory

        // if we're handling pre/post send actions (likely, is queued send) AND this recipient should be last to receive message
        if ($event_handling && $recipient->should_be_last_to_receive_message()) {
            self::handle_message_post_send($message);
        }
    }

    public static function handle_message_pre_send($message)
    {
        $message->set('is_sending', 1);
        $message->update();
    }

    public static function handle_message_post_send($message)
    {
        // send to any additional emails (if any)
        // self::send_additional_emails_for_message($message);

        // send receipt message (if applicable)
        // self::send_receipt_for_message($message);
        
        // update message as having been sent
        $message->set('is_sending', 0);
        $message->set('sent_at', time());
        $message->update();
    }

    /**
     * Updates the given draft message with the given form data
     * 
     * @param  message  $draft_message
     * @param  object  $form_data
     * @return message
     */
    private static function update_draft_message_with_course_compose_post($draft_message, $form_data)
    {
        // transform the form post data
        $posted = compose_request::get_transformed_post_data($form_data);

        // update attributes that may have changed from compose page
        $draft_message->set('alternate_email_id', $posted->alternate_email_id);
        $draft_message->set('subject', $posted->subject);
        $draft_message->set('body', $posted->message);
        $draft_message->set('output_channel', $posted->output_channel);
        $draft_message->set('signature_id', $posted->signature_id);
        $draft_message->set('send_receipt', $posted->receipt);
        $draft_message->set('to_send_at', $posted->to_send_at);
        $draft_message->set('is_draft', 0);
        $draft_message->update();
        
        // return a refreshed message record
        return $draft_message->read();
    }

    /**
     * Created a new message from the given form data
     * 
     * @param  object  $form_data
     * @param  object  $user  moodle user
     * @param  object  $course  moodle course
     * @return message
     */
    private static function create_message_from_course_compose_post($form_data, $user, $course)
    {
        // transform the form post data
        $posted = compose_request::get_transformed_post_data($form_data);

        // instantiate a message
        $message = new message(0, (object) [
            'course_id' => $course->id,
            'user_id' => $user->id,
            'output_channel' => $posted->output_channel,
            'alternate_email_id' => $posted->alternate_email_id,
            'signature_id' => $posted->signature_id,
            'subject' => $posted->subject,
            'body' => $posted->message,
            'send_receipt' => $posted->receipt,
            'to_send_at' => $posted->to_send_at
        ]);

        // save the message
        $message->create();

        return $message;
    }

    private static function send_additional_emails_for_message($message)
    {
        //
    }

    private static function send_receipt_for_message($message)
    {
        //
    }

}