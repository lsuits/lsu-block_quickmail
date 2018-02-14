<?php

namespace block_quickmail\persistents;

use block_quickmail_cache;
use \core\persistent;
use \lang_string;
use \dml_missing_record_exception;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_course;
use block_quickmail\persistents\concerns\belongs_to_a_user;
use block_quickmail\persistents\concerns\can_be_soft_deleted;
use block_quickmail\persistents\message_recipient;
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

		$recipients = message_recipient::get_records(['message_id' => $messageId]);

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
	 * Returns the message attachments that are associated with this message
	 *
	 * @return array
	 */
	public function get_message_attachments() {
		$messageId = $this->get('id');

		$attachments = message_attachment::get_records(['message_id' => $messageId]);

		return $attachments;
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
		return strip_tags($this->render_preview_string('body', $length, '...', '(No content)'));
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
	 * @param  bool    $is_draft  whether or not this is a draft message
	 * @return message
	 */
	public static function create_composed($user, $course, $data, $is_draft = false)
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
	 * @param  object  $data      transformed compose request data
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

}