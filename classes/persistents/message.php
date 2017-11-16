<?php

namespace block_quickmail\persistents;

use \core\persistent;
use \lang_string;
use \dml_missing_record_exception;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_course;
use block_quickmail\persistents\concerns\belongs_to_a_user;
use block_quickmail\persistents\concerns\can_be_soft_deleted;
use block_quickmail\persistents\message_recipient;
use block_quickmail\persistents\message_additional_email;
 
class message extends persistent {
 
    use enhanced_persistent,
        belongs_to_a_course,
        belongs_to_a_user,
        can_be_soft_deleted;

    /** Table name for the persistent. */
    const TABLE = 'block_quickmail_messages';
 
    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'course_id' => [
                'type' => PARAM_INT,
            ],
            'user_id' => [
                'type' => PARAM_INT,
            ],
            'alternate_email_id' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'signature_id' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'subject' => [
                'type' => PARAM_TEXT,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'body' => [
                'type' => PARAM_RAW,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'editor_format' => [
                'type' => PARAM_INT,
                'default' => 1, // @TODO - make this configurable?
            ],
            'sent_at' => [
                'type' => PARAM_INT,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'is_draft' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'timedeleted' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }

    ///////////////////////////////////////////////
    ///
    ///  RELATIONSHIPS
    /// 
    ///////////////////////////////////////////////

    /**
     * Returns the additional emails that are associated with this message
     *
     * @return array
     */
    public function get_additional_emails() {
        $messageId = $this->get('id');

        return message_additional_email::get_records(['message_id' => $messageId]);
    }

    ///////////////////////////////////////////////
    ///
    ///  SETTERS
    /// 
    ///////////////////////////////////////////////
    
    //

    ///////////////////////////////////////////////
    ///
    ///  GETTERS
    /// 
    ///////////////////////////////////////////////
    
    /**
     * Returns the status of this alternates approval (approved or waiting)
     * 
     * @return string
     */
    public function get_status() {
        
        //

    }
    

    ///////////////////////////////////////////////
    ///
    ///  VALIDATORS
    /// 
    ///////////////////////////////////////////////

    //

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM METHODS
    /// 
    ///////////////////////////////////////////////
    
    /**
     * Reports whether or not this message is a draft
     * 
     * @return bool
     */
    public function is_message_draft()
    {
        return (bool) $this->get('is_draft');
    }

    /**
     * Replaces all recipients for this message with the given array of user ids
     * 
     * @param  array  $recipient_user_ids
     * @return void
     */
    public function sync_recipients($recipient_user_ids = [])
    {
        // clear all current recipients
        message_recipient::clear_all_for_message($this);

        // add all new recipients
        foreach ($recipient_user_ids as $user_id) {
            message_recipient::create_for_message($this, ['user_id' => $user_id]);
        }
    }

    /**
     * Replaces all additional emails for this message with the given array of emails
     * 
     * @param  array  $additional_emails
     * @return void
     */
    public function sync_additional_emails($additional_emails = [])
    {
        // clear all current recipients
        message_additional_email::clear_all_for_message($this);

        // add all new recipients
        foreach ($additional_emails as $email) {
            message_additional_email::create_for_message($this, ['email' => $email]);
        }
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Fetches a draft message by id, or returns null
     * 
     * @param  int  $message_id
     * @return message|null
     */
    public static function find_draft_or_null($message_id)
    {
        // first, try to find the message by id, returning null by default
        if ( ! $message = self::find_or_null($message_id)) {
            return null;
        }

        // if this message is NOT a draft, return null
        if ( ! $message->is_message_draft()) {
            return null;
        }

        return $message;
    }

    /**
     * Fetches a message by id which must belong to the given user id, or returns null
     * 
     * @param  integer $message_id
     * @param  integer $user_id
     * @param  integer $course_id
     * @return message|null
     */
    public static function find_user_course_draft_or_null($message_id = 0, $user_id = 0, $course_id = 0)
    {
        // first, try to find the message by id, returning null by default
        if ( ! $message = self::find_draft_or_null($message_id)) {
            return null;
        }

        // if this message does not belong to this course, return null
        if ( ! $message->is_owned_by_course($course_id)) {
            return null;
        }

        // if this message does not belong to this user, return null
        if ( ! $message->is_owned_by_user($user_id)) {
            return null;
        }

        return $message;
    }
 
}