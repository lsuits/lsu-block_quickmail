<?php

namespace block_quickmail\messenger;

use block_quickmail_config;
use block_quickmail_emailer;
use block_quickmail\persistents\message;
use block_quickmail\persistents\alternate_email;
use block_quickmail\persistents\message_recipient;
use block_quickmail\persistents\message_additional_email;
use block_quickmail\validators\compose_message_form_validator;
use block_quickmail\validators\save_draft_message_form_validator;
use block_quickmail\requests\compose_request;
use block_quickmail\exceptions\validation_exception;
use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory;
use block_quickmail\filemanager\message_file_handler;
use block_quickmail\tasks\send_message_to_recipient_adhoc_task;
use core\task\manager as task_manager;
use block_quickmail\messenger\subject_prepender;

class messenger {

    public $message;

    public function __construct(message $message)
    {
        $this->message = $message;
    }

    /**
     * Creates a draft message from the given user within the given course using the given form data
     * 
     * Optionally, a draft message may be passed which will be updated rather than created anew
     *
     * @param  object   $user            moodle user sending the message
     * @param  object   $course          course in which this message is being sent
     * @param  array    $form_data       message parameters which will be validated
     * @param  message  $draft_message   a draft message (optional, defaults to null)
     * @return message
     * @throws validation_exception
     * @throws critical_exception
     */
    public static function save_draft($user, $course, $form_data, $draft_message = null)
    {
        // validate form data
        $validator = new save_draft_message_form_validator($form_data, [
            'course_config' => block_quickmail_config::_c('', $course)
        ]);
        $validator->validate();

        // if errors, throw exception
        if ($validator->has_errors()) {
            throw new validation_exception('Validation exception!', $validator->errors);
        }

        // get transformed (valid) post data
        $transformed_data = compose_request::get_transformed_post_data($form_data);

        // if draft message was passed
        if ( ! empty($draft_message)) {
            // if draft message was already sent (shouldn't happen)
            if ($draft_message->is_sent_message()) {
                throw new validation_exception('Critical exception!');
            }

            // update draft message, maintaining draft status
            $message = $draft_message->update_draft($transformed_data, true);
        } else {
            // create new message as draft
            $message = message::create_composed($user, $course, $transformed_data, true);
        }

        // @TODO: handle posted file attachments (moodle)
        
        // clear any existing recipients, and add those that have been recently submitted
        $message->sync_recipients($transformed_data->mailto_ids);

        // clear any existing additional emails, and add those that have been recently submitted
        $message->sync_additional_emails($transformed_data->additional_emails);
        
        // @TODO: sync posted attachments to message record
        
        return $message;
    }

    public static function duplicate_draft($draft_id, $user)
    {
        // get the draft to be duplicated
        if ( ! $original_draft = new message($draft_id)) {
            throw new validation_exception('Could not duplicate this draft. Please try again.');
        }

        // make sure it's a draft
        if ( ! $original_draft->is_message_draft()) {
            throw new validation_exception('Message must be a draft to duplicate.');
        }

        // check that the draft belongs to the given user id
        if ($original_draft->get('user_id') !== $user->id) {
            throw new validation_exception('Sorry, that draft does not belong to you and cannot be duplicated.');
        }

        // create a new draft message from the original's data
        $new_draft = message::create_new([
            'course_id' => $original_draft->get('course_id'),
            'user_id' => $original_draft->get('user_id'),
            'message_type' => $original_draft->get('message_type'),
            'alternate_email_id' => $original_draft->get('alternate_email_id'),
            'signature_id' => $original_draft->get('signature_id'),
            'subject' => $original_draft->get('subject'),
            'body' => $original_draft->get('body'),
            'editor_format' => $original_draft->get('editor_format'),
            'is_draft' => 1,
            'send_receipt' => $original_draft->get('send_receipt'),
            'no_reply' => $original_draft->get('no_reply'),
            'usermodified' => $user->id
        ]);

        // duplicate the message recipients
        foreach ($original_draft->get_message_recipients() as $recipient) {
            message_recipient::create_new([
                'message_id' => $new_draft->get('id'),
                'user_id' => $recipient->get('user_id'),
            ]);
        }

        // duplicate the message additional emails
        foreach ($original_draft->get_additional_emails() as $additional_email) {
            message_additional_email::create_new([
                'message_id' => $new_draft->get('id'),
                'email' => $additional_email->get('email'),
            ]);
        }

        return $new_draft;
    }

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
     * @param  bool     $send_as_tasks   if false, the message will be sent immediately
     * @return message
     * @throws validation_exception
     * @throws critical_exception
     */
    public static function compose($user, $course, $form_data, $draft_message = null, $send_as_tasks = true)
    {
        // validate form data
        $validator = new compose_message_form_validator($form_data);
        $validator->validate();

        // if errors, throw exception
        if ($validator->has_errors()) {
            throw new validation_exception('Validation exception!', $validator->errors);
        }

        // get transformed (valid) post data
        $transformed_data = compose_request::get_transformed_post_data($form_data);

        // if draft message was passed
        if ( ! empty($draft_message)) {
            // if draft message was already sent (shouldn't happen)
            if ($draft_message->is_sent_message()) {
                throw new validation_exception('Critical exception!');
            }

            // update draft message, and remove draft status
            $message = $draft_message->update_draft($transformed_data, false);
        } else {
            // create new message
            $message = message::create_composed($user, $course, $transformed_data);
        }

        // handle saving and syncing of any uploaded file attachments
        message_file_handler::handle_posted_attachments($message, $form_data, 'attachments');

        // clear any existing recipients, and add those that have been recently submitted
        $message->sync_recipients($transformed_data->mailto_ids);

        // clear any existing additional emails, and add those that have been recently submitted
        $message->sync_additional_emails($transformed_data->additional_emails);
        
        // if not scheduled for delivery later, send now
        if ( ! $message->get_to_send_in_future()) {
            self::deliver($message, $send_as_tasks);
        }

        return $message;
    }

    /**
     * Instantiates a messenger and performs the delivery of the given message to all of its recipients
     * By default, the message to recipient transactions will be queued to send as adhoc tasks
     * 
     * @param  message  $message     message to be sent
     * @param  bool     $queue_send  if false, the message will be sent immediately
     * @return bool
     */
    public static function deliver(message $message, $queue_send = true)
    {
        // is message is currently being sent, bail out
        if ($message->is_being_sent()) {
            return false;
        }

        $messenger = new self($message);

        return $messenger->send($queue_send);
    }
    
    /**
     * Sends the message to all of its recipients
     * 
     * @param  bool     $queue_send  if true, will send each delivery as an adhoc task
     * @return bool
     */
    public function send($queue_send = true)
    {
        // if sending now, handle pre-send actions
        if ( ! $queue_send) {
            $this->handle_message_pre_send();
        }

        // iterate through all message recipients
        foreach($this->message->get_message_recipients() as $recipient) {
            // if any exceptions are thrown, gracefully move to the next recipient
            try {
                if ( ! $queue_send) {
                    // send now
                    $this->send_to_recipient($recipient, false);
                } else {
                    // create a job
                    $task = new send_message_to_recipient_adhoc_task();

                    $task->set_custom_data([
                        'message_id' => $this->message->get('id'),
                        'recipient_id' => $recipient->get('id'),
                    ]);

                    // queue job
                    task_manager::queue_adhoc_task($task);
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // if sending now, handle post-send actions
        if ( ! $queue_send) {
            $this->handle_message_post_send();
        }

        return true;
    }

    /**
     * Sends the message to the given recipient
     * 
     * @param  message_recipient  $recipient       message recipient to recieve the message
     * @param  bool               $event_handling  if true, pre-send and post-send actions will be fired
     * @return bool
     */
    public function send_to_recipient($recipient, $event_handling = false)
    {
        // if we're handling pre/post send actions (likely, is queued send) AND this recipient should be first to receive message
        if ($event_handling && $recipient->should_be_first_to_receive_message()) {
            $this->handle_message_pre_send();
        }

        // instantiate recipient_send_factory
        $recipient_send_factory = recipient_send_factory::make($this->message, $recipient);

        // send recipient_send_factory
        $recipient_send_factory->send();

        // if we're handling pre/post send actions (likely, is queued send) AND this recipient should be last to receive message
        if ($event_handling && $recipient->should_be_last_to_receive_message()) {
            $this->handle_message_post_send();
        }

        return true;
    }

    /**
     * Performs pre-send actions
     * 
     * @return void
     */
    private function handle_message_pre_send()
    {
        $this->message->set('is_sending', 1);
        $this->message->update();
        $this->message->read(); // necessary?
    }

    /**
     * Performs post-send actions
     * 
     * @return void
     */
    private function handle_message_post_send()
    {
        // send to any additional emails (if any)
        $this->send_message_additional_emails();

        // send receipt message (if applicable)
        if ($this->message->get('send_receipt')) {
            $this->send_message_reciept();
        }
        
        // update message as having been sent
        $this->message->set('is_sending', 0);
        $this->message->set('sent_at', time());
        $this->message->update();
        $this->message->read(); // necessary?
    }

    /**
     * Sends an email to each of this message's additional emails (if any)
     * 
     * @return void
     */
    private function send_message_additional_emails()
    {
        $fromuser = $this->message->get_user();

        $subject = subject_prepender::format_course_subject(
            $this->message->get_course(), 
            $this->message->get('subject')
        );

        $body = $this->message->get('body'); // @TODO - find some way to clean out any custom data fields for this fake user (??)
        
        foreach($this->message->get_additional_emails() as $additional_email) {

            // instantiate an emailer
            $emailer = new block_quickmail_emailer($fromuser, $subject, $body);
            $emailer->to_email($additional_email->get('email'));

            // determine reply to parameters based off of message settings
            if ( ! (bool) $this->message->get('no_reply')) {
                // if the message has an alternate email, reply to that
                if ($alternate_email = alternate_email::find_or_null($this->message->get('alternate_email_id'))) {
                    $replyto_email = $alternate_email->get('email');
                    $replyto_name = $alternate_email->get_fullname();
                
                // otherwise, reply to sending user
                } else {
                    $replyto_email = $fromuser->email;
                    $replyto_name = fullname($fromuser);
                }

                $emailer->reply_to($replyto_email, $replyto_name);
            }

            // attempt to send the email
            if ($emailer->send()) {
                $additional_email->mark_as_sent();
            }
        }
    }

    /**
     * Sends an email receipt to the sending user, if necessary
     * 
     * @return void
     */
    private function send_message_reciept()
    {
        $fromuser = $this->message->get_user();

        $subject = subject_prepender::format_course_subject(
            $this->message->get_course(), 
            $this->message->get('subject')
        );

        $body = $this->message->get('body'); // @TODO - find some way to clean out any custom data fields for this fake user (??)
        
        // instantiate an emailer
        $emailer = new block_quickmail_emailer($fromuser, $subject, $body);
        $emailer->to_email($fromuser->email);

        // determine reply to parameters based off of message settings
        if ( ! (bool) $this->message->get('no_reply')) {
            $emailer->reply_to($fromuser->email, fullname($fromuser));
        }

        // attempt to send the email
        $emailer->send();
    }

}