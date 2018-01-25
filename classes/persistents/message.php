<?php

namespace block_quickmail\persistents;

use block_quickmail_plugin;
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
            'message_type' => [
                'type' => PARAM_TEXT,
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
                'default' => 0,
            ],
            'to_send_at' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'is_draft' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'send_receipt' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'is_sending' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'no_reply' => [
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
     * Optionally, returns an array of emails
     *
     * @return array
     */
    public function get_additional_emails($as_email_array = false) {
        $messageId = $this->get('id');

        $additionals = message_additional_email::get_records(['message_id' => $messageId]);

        if ( ! $as_email_array) {
            return $additionals;
        }

        $emails = array_reduce($additionals, function ($carry, $additional) {
            $carry[] = $additional->get('email');
            
            return $carry;
        }, []);

        return $emails;
    }

    /**
     * Returns the message recipients that are associated with this message
     *
     * Optionally, returns as an array of user ids
     *
     * @return array
     */
    public function get_message_recipients($as_user_id_array = false) {
        $messageId = $this->get('id');

        $recipients =  message_recipient::get_records(['message_id' => $messageId]);

        if ( ! $as_user_id_array) {
            return $recipients;
        }

        $recipient_ids = array_reduce($recipients, function ($carry, $recipient) {
            $carry[] = $recipient->get('user_id');
            
            return $carry;
        }, []);

        return $recipient_ids;
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
     * Returns the status of this message
     * 
     * @return string  deleted|drafted|queued|sending|sent
     */
    public function get_status() {
        if ($this->is_being_sent()) {
            return 'sending';
        }

        if ($this->is_soft_deleted()) {
            return 'deleted';
        }

        if ($this->is_message_draft()) {
            return 'drafted';
        }

        if ($this->is_queued_message()) {
            return 'queued';
        }
        
        return 'sent';
    }

    public function get_to_send_in_future() {
        return $this->get('to_send_at') > time();
    }

    public function get_subject_preview($length = 20) {
        return $this->render_preview_string('subject', $length, '...', '(No subject)');
    }

    public function get_body_preview($length = 40) {
        return $this->render_preview_string(strip_tags('body'), $length, '...', '(No content)');
    }

    public function get_readable_created_at() {
        return $this->get_readable_date('timecreated');
    }

    public function get_readable_last_modified_at() {
        return $this->get_readable_date('timemodified');
    }

    public function get_readable_sent_at() {
        return $this->get_readable_date('sent_at');
    }

    public function get_readable_to_send_at() {
        return $this->get_readable_date('to_send_at');
    }

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
     * Reports whether or not this message is queued to be sent
     * 
     * @return bool
     */
    public function is_queued_message()
    {
        return (bool) $this->get('to_send_at') !== 0 && ! $this->is_sent_message();
    }

    /**
     * Reports whether or not this message is marked as being sent at the moment
     * 
     * @return bool
     */
    public function is_being_sent()
    {
        return (bool) $this->get('is_sending');
    }

    /**
     * Reports whether or not this message is marked as sent
     * 
     * @return bool
     */
    public function is_sent_message()
    {
        return (bool) $this->get('sent_at');
    }

    ///////////////////////////////////////////////
    ///
    ///  VALIDATORS
    /// 
    ///////////////////////////////////////////////

    //

    ///////////////////////////////////////////////
    ///
    ///  COMPOSITION METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Creates a new message from the given sending user, course, and data
     * 
     * @param  object  $user  moodle user
     * @param  object  $course  moodle course
     * @param  object  $data  transformed compose request data
     * @return message
     */
    public static function create_composed($user, $course, $data)
    {
        // create a new message
        $message = self::create_new([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'message_type' => $data->message_type,
            'alternate_email_id' => $data->alternate_email_id,
            'signature_id' => $data->signature_id,
            'subject' => $data->subject,
            'body' => $data->message,
            'send_receipt' => $data->receipt,
            'to_send_at' => $data->to_send_at,
            'no_reply' => $data->no_reply
        ]);

        return $message;
    }

    /**
     * Updates this draft message with the given data, and removes its draft status
     * 
     * @param  object  $data  transformed compose request data
     * @return message
     */
    public function update_and_pull_draft($data)
    {
        $this->set('alternate_email_id', $data->alternate_email_id);
        $this->set('subject', $data->subject);
        $this->set('body', $data->message);
        $this->set('message_type', $data->message_type);
        $this->set('signature_id', $data->signature_id);
        $this->set('send_receipt', $data->receipt);
        $this->set('to_send_at', $data->to_send_at);
        $this->set('is_draft', 0);
        $this->update();
        
        // return a refreshed message record
        return $this->read();
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
            // if any exceptions, proceed gracefully to the next
            try {
                message_recipient::create_for_message($this, ['user_id' => $user_id]);
            } catch (\Exception $e) {
                // most likely invalid user, exception thrown due to validation error
                // log this?
                continue;
            }
        }

        // refresh record (necessary?)
        $this->read();
    }

    /**
     * Replaces all additional emails for this message with the given array of emails
     * 
     * @param  array  $additional_emails
     * @return void
     */
    public function sync_additional_emails($additional_emails = [])
    {
        // clear all current additional emails
        message_additional_email::clear_all_for_message($this);

        // add all new additional emails
        foreach ($additional_emails as $email) {
            // if the email is invalid, proceed gracefully to the next
            try {
                message_additional_email::create_for_message($this, ['email' => $email]);
            } catch (\Exception $e) {
                // most likely exception thrown due to validation error
                // log this?
                continue;
            }
        }

        // refresh record (necessary?)
        $this->read();
    }

    ///////////////////////////////////////////////
    ///
    ///  DATA-FETCHING STATIC METHODS
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
     * @return message|null
     */
    public static function find_user_draft_or_null($message_id = 0, $user_id = 0)
    {
        // first, try to find the message by id, returning null by default
        if ( ! $message = self::find_draft_or_null($message_id)) {
            return null;
        }

        // if this message does not belong to this user, return null
        if ( ! $message->is_owned_by_user($user_id)) {
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
        if ( ! $message = self::find_user_draft_or_null($message_id, $user_id)) {
            return null;
        }

        // if this message does not belong to this course, return null
        if ( ! $message->is_owned_by_course($course_id)) {
            return null;
        }

        return $message;
    }

    /**
     * Returns all unsent, non-deleted, draft messages belonging to the given user id
     *
     * Optionally, can be scoped to a specific course if given a course_id
     * 
     * @param  int     $user_id
     * @param  int     $course_id   optional, defaults to 0 (all)
     * @return array
     */
    public static function get_all_unsent_drafts_for_user($user_id, $course_id = 0)
    {
        $params = [
            'user_id' => $user_id, 
            'is_draft' => 1, 
            'sent_at' => 0, 
            'timedeleted' => 0
        ];

        if ($course_id) {
            $params['course_id'] = $course_id;
        }

        return self::get_records($params);
    }

    /**
     * Returns all sent or queued, non-deleted, messages belonging to the given user id
     *
     * @param  int     $user_id
     * @return array
     */
    public static function get_all_historical_for_user($user_id)
    {
        global $DB;
 
        $sql = 'SELECT DISTINCT m.*
                  FROM {' . static::TABLE . '} m
                  WHERE m.user_id = :user_id
                  AND m.is_draft = 0
                  AND m.timedeleted = 0
                  AND m.sent_at > 0
                  OR m.user_id = :user_id2
                  AND m.is_draft = 0
                  AND m.timedeleted = 0
                  AND m.sent_at = 0
                  AND m.to_send_at > 0';
     
        $persistents = [];
     
        $recordset = $DB->get_recordset_sql($sql, ['user_id' => $user_id, 'user_id2' => $user_id]);
        foreach ($recordset as $record) {
            $persistents[] = new static(0, $record);
        }
        $recordset->close();
     
        return $persistents;
    }
 
}