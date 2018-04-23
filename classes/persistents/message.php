<?php

namespace block_quickmail\persistents;

use \core\persistent;
use block_quickmail_cache;
use block_quickmail_string;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_course;
use block_quickmail\persistents\concerns\belongs_to_a_user;
use block_quickmail\persistents\concerns\can_be_soft_deleted;
use block_quickmail\persistents\message_recipient;
use block_quickmail\persistents\message_draft_recipient;
use block_quickmail\persistents\message_additional_email;
use block_quickmail\persistents\message_attachment;
 
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
		$message_id = $this->get('id');

		$additionals = message_additional_email::get_records(['message_id' => $message_id]);

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
		$message_id = $this->get('id');

		$recipients = message_recipient::get_records(['message_id' => $message_id]);

		if ( ! $as_user_id_array) {
			return $recipients;
		}

		$recipient_ids = array_reduce($recipients, function ($carry, $recipient) {
			$carry[] = $recipient->get('user_id');
			
			return $carry;
		}, []);

		return $recipient_ids;
	}

	/**
	 * Returns the message draft recipients that are associated with this message
	 *
	 * @return array
	 */
	public function get_message_draft_recipients($type = '', $as_key_array = false) {
		$message_id = $this->get('id');

		$params = [
			'message_id' => $message_id
		];

		if ($type) {
			$params['type'] = $type;
		}

		$recipients = message_draft_recipient::get_records($params);

		if ( ! $as_key_array) {
			return $recipients;
		}

		$key_array = array_map(function($recipient) {
			return $recipient->get_recipient_key();
		}, $recipients);

		return $key_array;
	}

	/**
	 * Returns the message attachments that are associated with this message
	 *
	 * @return array
	 */
	public function get_message_attachments() {
		$message_id = $this->get('id');

		$attachments = message_attachment::get_records(['message_id' => $message_id]);

		return $attachments;
	}

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
			return block_quickmail_string::get('sending');
		}

		if ($this->is_soft_deleted()) {
			return block_quickmail_string::get('deleted');
		}

		if ($this->is_message_draft()) {
			return block_quickmail_string::get('drafted');
		}

		if ($this->is_queued_message()) {
			return block_quickmail_string::get('queued');
		}
		
		return block_quickmail_string::get('sent');
	}

	public function get_to_send_in_future() {
		return $this->get('to_send_at') > time();
	}

	public function get_subject_preview($length = 20) {
		return $this->render_preview_string('subject', $length, '...', block_quickmail_string::get('preview_no_subject'));
	}

	public function get_body_preview($length = 40) {
		return strip_tags($this->render_preview_string('body', $length, '...', block_quickmail_string::get('preview_no_body')));
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
		$to_be_sent = (bool) $this->get('to_send_at');

		return (bool) $to_be_sent && ! $this->is_sent_message();
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
	 * Reports whether or not this message needs to send a receipt email
	 * 
	 * @return bool
	 */
	public function should_send_receipt()
	{
		return (bool) $this->get('send_receipt');
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

	/**
	 * Returns the cached intended recipient count total for this message
	 *
	 * Attempts to set the total in the cache if not found
	 * 
	 * @return int
	 */
	public function cached_recipient_count()
	{
		$message = $this;

		return (int) block_quickmail_cache::store('qm_msg_recip_count')->add($this->get('id'), function() use ($message) {
			return count($message->get_message_recipients());
		});
	}

	/**
	 * Returns the cached intended additional email count total for this message
	 *
	 * Attempts to set the total in the cache if not found
	 * 
	 * @return int
	 */
	public function cached_additional_email_count()
	{
		$message = $this;

		return (int) block_quickmail_cache::store('qm_msg_addl_email_count')->add($this->get('id'), function() use ($message) {
			return count($message->get_additional_emails());
		});
	}

	/**
	 * Returns the cached file attachment count total for this message
	 *
	 * Attempts to set the total in the cache if not found
	 * 
	 * @return int
	 */
	public function cached_attachment_count()
	{
		$message = $this;

		return (int) block_quickmail_cache::store('qm_msg_attach_count')->add($this->get('id'), function() use ($message) {
			return count($message->get_message_attachments());
		});
	}

	///////////////////////////////////////////////
	///
	///  SETTERS
	/// 
	///////////////////////////////////////////////

	/**
	 * Update this message as having sent a receipt message
	 * 
	 * @return void
	 */
	public function mark_receipt_as_sent()
	{
		$this->set('send_receipt', 0);
        
        $this->update();
	}

	///////////////////////////////////////////////
	///
	///  PERSISTENT HOOKS
	/// 
	///////////////////////////////////////////////
	
	/**
	 * After delete hook
	 * 
	 * @param  bool  $result
	 * @return void
	 */
	protected function after_delete($result)
	{
		// if this was a draft message (which are hard deleted), delete all related data
		if ($this->is_message_draft()) {
			message_recipient::clear_all_for_message($this);
			message_draft_recipient::clear_all_for_message($this);
			message_additional_email::clear_all_for_message($this);
			message_attachment::clear_all_for_message($this);
		}
	}

	///////////////////////////////////////////////
	///
	///  COMPOSITION METHODS
	/// 
	///////////////////////////////////////////////

	/**
	 * Creates a new "compose" (course-scoped) or "broadcast" (site-scoped) message from the given sending user, course, and data
	 * 
	 * @param  string  $type  compose|broadcast
	 * @param  object  $user  moodle user
	 * @param  object  $course  moodle course
	 * @param  object  $data  transformed compose request data
	 * @param  bool    $is_draft  whether or not this is a draft message
	 * @return message
	 */
	public static function create_type($type, $user, $course, $data, $is_draft = false)
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
			'no_reply' => $data->no_reply,
			'is_draft' => (int) $is_draft
		]);

		return $message;
	}

	/**
	 * Updates this draft message with the given data
	 * 
	 * @param  object  $data      transformed compose/broadcast request data
	 * @param  bool    $is_draft  whether or not this draft is still a draft after this update
	 * @return message
	 */
	public function update_draft($data, $is_draft = null)
	{
		if (is_null($is_draft)) {
			$is_draft = $this->is_message_draft();
		}

		$this->set('alternate_email_id', $data->alternate_email_id);
		$this->set('subject', $data->subject);
		$this->set('body', $data->message);
		$this->set('message_type', $data->message_type);
		$this->set('signature_id', $data->signature_id);
		$this->set('send_receipt', $data->receipt);
		$this->set('to_send_at', $data->to_send_at);
		$this->set('no_reply', $data->no_reply);
		$this->set('is_draft', (bool) $is_draft);
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

		$count = 0;

		// add all new recipients
		foreach ($recipient_user_ids as $user_id) {
			// if any exceptions, proceed gracefully to the next
			try {
				message_recipient::create_for_message($this, ['user_id' => $user_id]);
				$count++;
			} catch (\Exception $e) {
				// most likely invalid user, exception thrown due to validation error
				// log this?
				continue;
			}
		}

		// cache the count for external use
		block_quickmail_cache::store('qm_msg_recip_count')->put($this->get('id'), $count);

		// refresh record (necessary?)
		$this->read();
	}

	/**
	 * Replaces all "draft recipients" for this message with the given arrays of entity keys
	 * 
	 * @param  array  $include_key_container  [role_*, group_*, user_*]
	 * @return void
	 */
	public function sync_draft_recipients($include_key_container = [], $exclude_key_container = [])
	{
		// clear all current draft recipients
		message_draft_recipient::clear_all_for_message($this);

		// iterate through allowed "inclusion types"
		foreach (['include', 'exclude'] as $type) {
			$key_container = $type . '_key_container';

			// iterate through the given named key container
			foreach ($$key_container as $key) {
				$exploded = explode('_', $key);

				// if the key was a valid value
				if (count($exploded) == 2 && in_array($exploded[0], ['role', 'group', 'user'])) {
					// set the attributes appropriately
					$recipient_type = $exploded[0];
					$recipient_id = $exploded[1];

					// if the id is (potentially) valid
					if (is_numeric($recipient_id)) {
						// create a record
						message_draft_recipient::create_for_message($this, [
							'type' => $type,
							'recipient_type' => $recipient_type,
							'recipient_id' => $recipient_id,
						]);
					}
				}
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

		$count = 0;

		// add all new additional emails
		foreach ($additional_emails as $email) {
			// if the email is invalid, proceed gracefully to the next
			try {
				message_additional_email::create_for_message($this, ['email' => $email]);
				$count++;
			} catch (\Exception $e) {
				// most likely exception thrown due to validation error
				// log this?
				continue;
			}
		}

		// cache the count for external use
		block_quickmail_cache::store('qm_msg_addl_email_count')->put($this->get('id'), $count);

		// refresh record (necessary?)
		$this->read();
	}

	/**
	 * Unqueue this message and move to draft status
	 * 
	 * @return message
	 */
	public function unqueue()
	{
		$this->set('to_send_at', 0);
		$this->set('is_draft', true);
		$this->update();
		
		// return a refreshed message record
		return $this->read();
	}

	/**
	 * Updates this message to be sent now rather than later
	 * 
	 * @return message
	 */
	public function changed_queued_to_now()
	{
		$this->set('to_send_at', 0);
		$this->update();

		// return a refreshed message record
		return $this->read();
	}

	///////////////////////////////////////////////
	///
	///  UTILITIES
	/// 
	///////////////////////////////////////////////

	/**
     * Returns an array of messages belonging to a specific course given an array of messages and course id
     * 
     * @param  array  $messages
     * @param  int    $course_id
     * @return array
     */
    public static function filter_messages_by_course($messages, $course_id) {
        if ($course_id) {
            // if a course is selected, filter out any not belonging to the course and return
            return array_filter($messages, function($msg) use ($course_id) {
                return $msg->get('course_id') == $course_id;
            });
        }

        // otherwise, include all messages
        return $messages;
    }

    /**
     * Returns an array of user course data given an array of messages
     * This will include the currently selected course, even if that course does not have any messages
     * 
     * @param  array  $messages
     * @param  int    $selected_course_id
     * @return array  [course id => course short name]
     */
    public static function get_user_course_array($messages, $selected_course_id = 0) {
        global $DB;
        
        // first, get all course ids from the given messages
        $course_ids = array_reduce($messages, function($carry, $message) {
            $carry[] = (int) $message->get('course_id');

            return $carry;
        }, []);

        // if a selected course id was given, be sure to include this course in the results
        if ($selected_course_id) {
            $course_ids[] = $selected_course_id;
        }

        // make sure we have unique values
        $course_ids = array_unique($course_ids, SORT_NUMERIC);

        // get course data for the given list of course ids
        $course_data = $DB->get_records_sql('SELECT id, shortname FROM {course} WHERE id in (' . implode(',', $course_ids) . ')');

        $results = [];

        // add an entry for each course to the results array
        foreach ($course_data as $course) {
            $results[(int) $course->id] = $course->shortname;
        }

        return $results;
    }

}