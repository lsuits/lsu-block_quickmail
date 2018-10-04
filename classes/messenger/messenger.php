<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\messenger;

use block_quickmail\messenger\messenger_interface;
use block_quickmail_config;
use block_quickmail_plugin;
use block_quickmail_string;
use block_quickmail_emailer;
use block_quickmail\persistents\message;
use block_quickmail\persistents\alternate_email;
use block_quickmail\persistents\message_recipient;
use block_quickmail\persistents\message_draft_recipient;
use block_quickmail\persistents\message_additional_email;
use block_quickmail\validators\message_form_validator;
use block_quickmail\validators\save_draft_message_form_validator;
use block_quickmail\requests\compose_request;
use block_quickmail\requests\broadcast_request;
use block_quickmail\exceptions\validation_exception;
use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory;
use block_quickmail\filemanager\message_file_handler;
use block_quickmail\filemanager\attachment_appender;
use block_quickmail\messenger\message\subject_prepender;
use block_quickmail\messenger\message\signature_appender;
use block_quickmail\repos\user_repo;
use moodle_url;
use html_writer;

class messenger implements messenger_interface {

    public $message;
    public $all_profile_fields;
    public $selected_profile_fields;

    public function __construct(message $message)
    {
        $this->message = $message;
        $this->all_profile_fields = block_quickmail_plugin::get_user_profile_field_array();
        $this->selected_profile_fields = block_quickmail_config::block('email_profile_fields');
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  MESSAGE COMPOSITION METHODS
    /// 
    /////////////////////////////////////////////////////////////

    /**
     * Creates a "compose" (course-scoped) message from the given user within the given course using the given form data
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
        // validate basic message form data
        self::validate_message_form_data($form_data, 'compose');

        // get transformed (valid) post data
        $transformed_data = compose_request::get_transformed_post_data($form_data);

        // get a message instance for this type, either from draft or freshly created
        $message = self::get_message_instance('compose', $user, $course, $transformed_data, $draft_message, false);
        
        // get only the resolved recipient user ids
        $recipient_user_ids = user_repo::get_unique_course_user_ids_from_selected_entities(
            $course, 
            $user, 
            $transformed_data->included_entity_ids, 
            $transformed_data->excluded_entity_ids
        );

        return self::send_message_to_recipients($message, $form_data, $recipient_user_ids, $transformed_data->additional_emails, $send_as_tasks);
    }

    /**
     * Creates an "broadcast" (admin, site-scoped) message from the given user using the given user filter and form data
     * 
     * Depending on the given form data, this message may be sent now or at some point in the future.
     * By default, the message delivery will be handled as individual adhoc tasks which are
     * picked up by a scheduled task.
     *
     * Optionally, a draft message may be passed which will use and update the draft information
     *
     * @param  object                                       $user                         moodle user sending the message
     * @param  object                                       $course                       the moodle "SITEID" course
     * @param  array                                        $form_data                    message parameters which will be validated
     * @param  block_quickmail_broadcast_recipient_filter   $broadcast_recipient_filter
     * @param  message                                      $draft_message                a draft message (optional, defaults to null)
     * @param  bool                                         $send_as_tasks                if false, the message will be sent immediately
     * @return message
     * @throws validation_exception
     * @throws critical_exception
     */
    public static function broadcast($user, $course, $form_data, $broadcast_recipient_filter, $draft_message = null, $send_as_tasks = true)
    {
        // validate basic message form data
        self::validate_message_form_data($form_data, 'broadcast');

        // be sure that we have at least one recipient from the given recipient filter results
        if ( ! $broadcast_recipient_filter->get_result_user_count()) {
            throw new validation_exception(block_quickmail_string::get('validation_exception_message'), block_quickmail_string::get('no_included_recipients_validation'));
        }

        // get transformed (valid) post data
        $transformed_data = broadcast_request::get_transformed_post_data($form_data);

        // get a message instance for this type, either from draft or freshly created
        $message = self::get_message_instance('broadcast', $user, $course, $transformed_data, $draft_message, false);

        // get the filtered recipient user ids
        $recipient_user_ids = $broadcast_recipient_filter->get_result_user_ids();

        return self::send_message_to_recipients($message, $form_data, $recipient_user_ids, $transformed_data->additional_emails, $send_as_tasks);
    }

    /**
     * Handles sending a given message to the given recipient user ids
     *
     * This will clear any draft-related data for the message, and sync it's recipients/additional emails
     *
     * @param  message  $message              message object instance being sent
     * @param  array    $form_data            posted moodle form data (used for file attachment purposes)
     * @param  array    $recipient_user_ids   moodle user ids to receive the message
     * @param  array    $additional_emails    array of additional email addresses to send to, optional, defaults to empty
     * @param  bool     $send_as_tasks        if false, the message will be sent immediately
     * @return message
     * @throws critical_exception
     */
    private static function send_message_to_recipients($message, $form_data, $recipient_user_ids = [], $additional_emails, $send_as_tasks = true)
    {
        // handle saving and syncing of any uploaded file attachments
        message_file_handler::handle_posted_attachments($message, $form_data, 'attachments');
        
        // clear any draft recipients for this message, unnecessary at this point
        message_draft_recipient::clear_all_for_message($message);

        // clear any existing recipients, and add those that have been recently submitted
        $message->sync_recipients($recipient_user_ids);

        // clear any existing additional emails, and add those that have been recently submitted
        $message->sync_additional_emails($additional_emails);
        
        // if not scheduled for delivery later
        if ( ! $message->get_to_send_in_future()) {
            // get the block's configured "send now threshold" setting
            $send_now_threshold = (int) block_quickmail_config::get('send_now_threshold');
            
            // if not configured to send as tasks OR the number of recipients is below the send now threshold
            if ( ! $send_as_tasks || ( ! empty($send_now_threshold) && count($recipient_user_ids) <= $send_now_threshold)) {
                // begin sending now
                $message->mark_as_sending();
                $messenger = new self($message);
                $messenger->send();

                return $message->read();
            }
        }

        return $message;
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  MESSAGE DRAFTING METHODS
    /// 
    /////////////////////////////////////////////////////////////

    /**
     * Creates a draft "compose" (course-scoped) message from the given user within the given course using the given form data
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
    public static function save_compose_draft($user, $course, $form_data, $draft_message = null)
    {
        self::validate_draft_form_data($form_data, 'compose');

        // get transformed (valid) post data
        $transformed_data = compose_request::get_transformed_post_data($form_data);

        // get a message instance for this type, either from draft or freshly created
        $message = self::get_message_instance('compose', $user, $course, $transformed_data, $draft_message, true);

        // @TODO: handle posted file attachments (moodle)

        // clear any existing draft recipients, and add those that have been recently submitted
        $message->sync_compose_draft_recipients($transformed_data->included_entity_ids, $transformed_data->excluded_entity_ids);
        
        // get only the resolved recipient user ids
        $recipient_user_ids = user_repo::get_unique_course_user_ids_from_selected_entities(
            $course, 
            $user, 
            $transformed_data->included_entity_ids, 
            $transformed_data->excluded_entity_ids
        );

        // clear any existing recipients, and add those that have been recently submitted
        $message->sync_recipients($recipient_user_ids);

        // clear any existing additional emails, and add those that have been recently submitted
        $message->sync_additional_emails($transformed_data->additional_emails);
        
        // @TODO: sync posted attachments to message record
        
        return $message;
    }
    
    /**
     * Creates a draft "broadcast" (system-scoped) message from the given user within the given course using the given form data
     * 
     * Optionally, a draft message may be passed which will be updated rather than created anew
     *
     * @param  object                                       $user            moodle user sending the message
     * @param  object                                       $course          course in which this message is being sent
     * @param  array                                        $form_data       message parameters which will be validated
     * @param  block_quickmail_broadcast_recipient_filter   $broadcast_recipient_filter
     * @param  message                                      $draft_message   a draft message (optional, defaults to null)
     * @return message
     * @throws validation_exception
     * @throws critical_exception
     */
    public static function save_broadcast_draft($user, $course, $form_data, $broadcast_recipient_filter, $draft_message = null)
    {
        self::validate_draft_form_data($form_data, 'broadcast');

        // get transformed (valid) post data
        $transformed_data = broadcast_request::get_transformed_post_data($form_data);

        // get a message instance for this type, either from draft or freshly created
        $message = self::get_message_instance('broadcast', $user, $course, $transformed_data, $draft_message, true);

        // @TODO: handle posted file attachments (moodle)
        
        // clear any existing draft recipient filters, and add this recently submitted value
        $message->sync_broadcast_draft_recipients($broadcast_recipient_filter->get_filter_value());
        
        // get the filtered recipient user ids
        $recipient_user_ids = $broadcast_recipient_filter->get_result_user_ids();

        // clear any existing recipients, and add those that have been recently submitted
        $message->sync_recipients($recipient_user_ids);

        // clear any existing additional emails, and add those that have been recently submitted
        $message->sync_additional_emails($transformed_data->additional_emails);
        
        // @TODO: sync posted attachments to message record
        
        return $message;
    }

    /**
     * Creates and returns a new message given a draft message id
     * 
     * @param  int    $draft_id
     * @param  object $user       the user duplicating the draft
     * @return message
     */
    public static function duplicate_draft($draft_id, $user)
    {
        // get the draft to be duplicated
        if ( ! $original_draft = new message($draft_id)) {
            throw new validation_exception(block_quickmail_string::get('could_not_duplicate'));
        }

        // make sure it's a draft
        if ( ! $original_draft->is_message_draft()) {
            throw new validation_exception(block_quickmail_string::get('must_be_draft_to_duplicate'));
        }

        // check that the draft belongs to the given user id
        if ( ! $original_draft->is_owned_by_user($user->id)) {
            throw new validation_exception(block_quickmail_string::get('must_be_owner_to_duplicate'));
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

        // duplicate the message draft recipients
        foreach ($original_draft->get_message_draft_recipients() as $recipient) {
            message_draft_recipient::create_new([
                'message_id' => $new_draft->get('id'),
                'type' => $recipient->get('type'),
                'recipient_type' => $recipient->get('recipient_type'),
                'recipient_id' => $recipient->get('recipient_id'),
                'recipient_filter' => $recipient->get('recipient_filter'),
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
     * Creates and returns a new message given a message id
     *
     * Note: this does not duplicate the intended recipient data
     * 
     * @param  int     $message_id
     * @param  object  $user         the user duplicating the message
     * @return message
     */
    public static function duplicate_message($message_id, $user)
    {
        // get the message to be duplicated
        if ( ! $original_message = new message($message_id)) {
            throw new validation_exception(block_quickmail_string::get('could_not_duplicate'));
        }

        // make sure it's not a draft
        if ($original_message->is_message_draft()) {
            throw new validation_exception(block_quickmail_string::get('could_not_duplicate'));
        }

        // check that the message belongs to the given user id
        if ( ! $original_message->is_owned_by_user($user->id)) {
            throw new validation_exception(block_quickmail_string::get('must_be_owner_to_duplicate'));
        }

        // create a new draft message from the original's data
        $new_draft = message::create_new([
            'course_id' => $original_message->get('course_id'),
            'user_id' => $original_message->get('user_id'),
            'message_type' => $original_message->get('message_type'),
            'alternate_email_id' => $original_message->get('alternate_email_id'),
            'signature_id' => $original_message->get('signature_id'),
            'subject' => $original_message->get('subject'),
            'body' => $original_message->get('body'),
            'editor_format' => $original_message->get('editor_format'),
            'is_draft' => 1,
            'send_receipt' => $original_message->get('send_receipt'),
            'no_reply' => $original_message->get('no_reply'),
            'usermodified' => $user->id
        ]);

        // duplicate the message additional emails
        foreach ($original_message->get_additional_emails() as $additional_email) {
            message_additional_email::create_new([
                'message_id' => $new_draft->get('id'),
                'email' => $additional_email->get('email'),
            ]);
        }

        return $new_draft;
    }

    /**
     * Validates message form data for a given message "type" (compose/broadcast)
     * 
     * @param  array   $form_data   message parameters which will be validated
     * @param  string  $type        compose|broadcast
     * @return void
     * @throws validation_exception
     */
    private static function validate_message_form_data($form_data, $type)
    {
        $extra_params = $type == 'broadcast'
            ? ['is_broadcast_message' => true]
            : [];

        // validate form data
        $validator = new message_form_validator($form_data, $extra_params);
        $validator->validate();

        // if errors, throw exception
        if ($validator->has_errors()) {
            throw new validation_exception(block_quickmail_string::get('validation_exception_message'), $validator->errors);
        }
    }

    /**
     * Validates draft message form data for a given message "type" (compose/broadcast)
     * 
     * @param  array   $form_data   message parameters which will be validated
     * @param  string  $type        compose|broadcast
     * @return void
     * @throws validation_exception
     */
    private static function validate_draft_form_data($form_data, $type)
    {
        $extra_params = $type == 'broadcast'
            ? ['is_broadcast_message' => true]
            : [];

        // validate form data
        $validator = new save_draft_message_form_validator($form_data, $extra_params);
        $validator->validate();

        // if errors, throw exception
        if ($validator->has_errors()) {
            throw new validation_exception(block_quickmail_string::get('validation_exception_message'), $validator->errors);
        }
    }

    /**
     * Returns a message object instance of the given type from the given params
     *
     * If a draft message is passed, the draft message will be updated to "non-draft" status and returned
     * otherwise, a new message instance will be created with the given user, course, and posted data
     * 
     * @param  string  $type               compose|broadcast
     * @param  object  $user               auth user creating the message
     * @param  object  $course             scoped course for this message
     * @param  object  $transformed_data   transformed posted form data
     * @param  message $draft_message
     * @param  bool    $is_draft           whether or not this instance is being resolved for purposes of saving as draft
     * @return message
     */
    private static function get_message_instance($type, $user, $course, $transformed_data, $draft_message = null, $is_draft = false)
    {
        // if draft message was passed
        if ( ! empty($draft_message)) {
            // if draft message was already sent (shouldn't happen)
            if ($draft_message->is_sent_message()) {
                throw new validation_exception(block_quickmail_string::get('critical_error'));
            }

            // update draft message, and remove draft status
            $message = $draft_message->update_draft($transformed_data, $is_draft);
        } else {
            // create new message
            $message = message::create_type($type, $user, $course, $transformed_data, $is_draft);
        }

        return $message;
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  MESSENGER INSTANCE METHODS
    /// 
    /////////////////////////////////////////////////////////////

    /**
     * Sends the message to all of its recipients
     * 
     * @return void
     */
    public function send()
    {
        // iterate through all message recipients
        foreach($this->message->get_message_recipients() as $recipient) {
            // if any exceptions are thrown, gracefully move to the next recipient
            if (!$recipient->has_been_sent_to()) {
                try {
                    // send to recipient now
                   $this->send_to_recipient($recipient);
                } catch (\Exception $e) {
                    // TODO: handle a failed send here?
                    continue;
                }
            }
        }
        
        $this->handle_message_post_send();
    }

    /**
     * Sends the message to the given recipient
     * 
     * @param  message_recipient  $recipient   message recipient to recieve the message
     * @return bool
     */
    public function send_to_recipient($recipient)
    {
        // instantiate recipient_send_factory
        $recipient_send_factory = recipient_send_factory::make($this->message, $recipient, $this->all_profile_fields, $this->selected_profile_fields);

        // send recipient_send_factory
        $recipient_send_factory->send();

        return true;
    }

    /**
     * Performs post-send actions
     * 
     * @return void
     */
    public function handle_message_post_send()
    {
        // send to any additional emails (if any)
        $this->send_message_additional_emails();

        // send receipt message (if applicable)
        if ($this->message->should_send_receipt()) {
            $this->send_message_receipt();
        }
        
        // update message as having been sent
        $this->message->set('is_sending', 0);
        $this->message->set('sent_at', time());
        $this->message->update();
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
        
        // append a signature to the formatted body, if appropriate
        $body = signature_appender::append_user_signature_to_body(
            $body, 
            $fromuser->id,
            $this->message->get('signature_id')
        );

        // append attachment download links to the formatted body, if any
        $body = attachment_appender::add_download_links($this->message, $body);

        foreach($this->message->get_additional_emails() as $additional_email) {
            if ( ! $additional_email->has_been_sent_to()) {
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
    }

    /**
     * Sends an email receipt to the sending user, if necessary
     * 
     * @return void
     */
    private function send_message_receipt()
    {
        $fromuser = $this->message->get_user();

        $subject = subject_prepender::format_for_receipt_subject(
            $this->message->get('subject')
        );

        $body = $this->get_receipt_message_body();
        
        // instantiate an emailer
        $emailer = new block_quickmail_emailer($fromuser, $subject, $body);
        $emailer->to_email($fromuser->email);

        // determine reply to parameters based off of message settings
        if ( ! (bool) $this->message->get('no_reply')) {
            $emailer->reply_to($fromuser->email, fullname($fromuser));
        }

        // attempt to send the email
        $emailer->send();

        // flag message as having sent the receipt message
        $this->message->mark_receipt_as_sent();
    }

    /**
     * Returns a body of text content for this message's send receipt
     * 
     * @return string
     */
    private function get_receipt_message_body()
    {
        $data = (object) [];

        // get any additional emails as a single string
        if ($additional_emails = $this->message->get_additional_emails(true)) {
            $addition_emails_string = implode(', ', $additional_emails);
        } else {
            $addition_emails_string = get_string('none');
        }

        // get subject with any prepend
        $data->subject = subject_prepender::format_for_receipt_subject(
            $this->message->get('subject')
        );

        $data->course_name = $this->message->get_course_property('fullname', ''); // TODO - format this course name based off of preference?
        $data->message_body = $this->message->get('body');
        $data->recipient_count = $this->message->cached_recipient_count();
        $data->sent_to_mentors = $this->message->get('send_to_mentors') ? get_string('yes') : get_string('no');
        $data->addition_emails_string = $addition_emails_string;
        $data->attachment_count = $this->message->cached_attachment_count();
        $data->sent_message_link = html_writer::link(new moodle_url('/blocks/quickmail/message.php', ['id' => $this->message->get('id')]), block_quickmail_string::get('here'));

        return block_quickmail_string::get('receipt_email_body', $data);
    }

}
