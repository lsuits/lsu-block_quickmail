<?php

namespace block_quickmail\messenger;

use block_quickmail\persistents\message;
use block_quickmail\validators\compose_message_form_validator;
use block_quickmail\requests\compose as compose_request;
use block_quickmail\exceptions\validation_exception;
use block_quickmail\exceptions\critical_exception;
use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory;
use block_quickmail\tasks\send_course_message_to_recipient_adhoc_task;
use core\task\manager as task_manager;

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
    // to_send_at
    // receipt
    // no_reply

    /**
     * Creates a message from the given user within the given course using the given form data
     * 
     * Depending on the given form data, this message may be sent now or at some point in the future.
     * By default, the message delivery will be handled as individual adhoc tasks which are
     * picked up by a scheduled task.
     *
     * Optionally, a draft message may be passed which will use and update the draft information
     *
     * @param  object   $user            moodle user sending the message
     * @param  object   $course          course in which this message is being sent
     * @param  array    $form_data       message parameters which will be validated
     * @param  message  $draft_message   a draft message (optional, defaults to null)
     * @param  bool     $queue_send      if true, the message will be sent immediately
     * @return bool
     * @throws validation_exception
     * @throws critical_exception
     */
    public static function send_composed_course_message($user, $course, $form_data, $draft_message = null, $queue_send = true)
    {
        // validate form data
        $validator = new compose_message_form_validator($form_data, [
            'course_config' => \block_quickmail_config::_c('', $course)
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

        // if sending immediately, send!
        if ( ! $message->get_to_send_in_future()) {
            self::execute_course_message($message, $queue_send);
        }
        
        // if no exceptions, send positive response
        return true;
    }

    /**
     * Performs the delivery of the given message to all of its recipients
     *
     * By default, the message to recipient transactions will be sent immediately,
     * however, may also be queued to send as adhoc tasks
     * 
     * @param  message  $message     message to be sent
     * @param  bool     $queue_send  if true, will send each delivery as an adhoc task
     * @return void
     */
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
                // send now
                self::send_course_message_to_recipient($message, $recipient, false);
            } else {
                // queue send
                $task = new send_course_message_to_recipient_adhoc_task();

                $task->set_custom_data([
                    'message_id' => $message->get('id'),
                    'recipient_id' => $recipient->get('id'),
                ]);

                task_manager::queue_adhoc_task($task);
            }
        }
        
        // if sending now, handle post-send actions
        if ( ! $queue_send) {
            self::handle_message_post_send($message);
        }
    }

    /**
     * Delivers the given message to the given recipient
     * 
     * @param  message            $message         message to be sent
     * @param  message_recipient  $recipient       message recipient to recieve the message
     * @param  bool               $event_handling  if true, pre-send and post-send actions will be fired
     * @return void
     */
    public static function send_course_message_to_recipient($message, $recipient, $event_handling = false)
    {
        // if we're handling pre/post send actions (likely, is queued send) AND this recipient should be first to receive message
        if ($event_handling && $recipient->should_be_first_to_receive_message()) {
            self::handle_message_pre_send($message);
        }

        // instantiate recipient_send_factory
        $recipient_send_factory = recipient_send_factory::make($message, $recipient);

        // send recipient_send_factory
        $recipient_send_factory->send();

        // if we're handling pre/post send actions (likely, is queued send) AND this recipient should be last to receive message
        if ($event_handling && $recipient->should_be_last_to_receive_message()) {
            self::handle_message_post_send($message);
        }
    }

    /**
     * Performs pre-send actions for the given message
     * 
     * @param  message  $message
     * @return void
     */
    public static function handle_message_pre_send($message)
    {
        $message->set('is_sending', 1);
        $message->update();
    }

    /**
     * Performs post-send actions for the given message
     * 
     * @param  message  $message
     * @return void
     */
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
     * Creates a new message from the given form data
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