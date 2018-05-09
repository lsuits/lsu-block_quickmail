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

/**
 * function to migrate quickmail history files attachment to the new file version from 1.9 to 2.x
 */
function migrate_quickmail_20(){
	global $DB;
	//migration of attachments
	$fs = get_file_storage();
	$quickmail_log_records=$DB->get_records_select('block_quickmail_log','attachment<>\'\'');
	foreach($quickmail_log_records as $quickmail_log_record){
		//searching file into mdl_files
		//analysing attachment content
		$filename=$quickmail_log_record->attachment;
		$filepath='';
		$notrootfile=strstr($quickmail_log_record->attachment,'/');		
		if($notrootfile){
			$filename=substr($quickmail_log_record->attachment,strrpos($quickmail_log_record->attachment, '/',-1)+1);
			$filepath='/'.substr($quickmail_log_record->attachment,0,strrpos($quickmail_log_record->attachment,'/',-1)+1);
		}else{
			$filepath='/';
			$filename=$quickmail_log_record->attachment;
		}
		$fs = get_file_storage();
                $coursecontext = context_course::instance($quickmail_log_record->courseid);

		$coursefile=$fs->get_file($coursecontext->id, 'course', 'legacy', 0, $filepath, $filename);
		if($coursefile){
			if($notrootfile){
				//rename
				$filename=str_replace('/', '_', $quickmail_log_record->attachment);
				$filepath='/';
				$quickmail_log_record->attachment=$filename;
				$DB->update_record('block_quickmail_log', $quickmail_log_record);
			}
			$file_record = array('contextid'=>$coursecontext->id, 'component'=>'block_quickmail', 'filearea'=>'attachment_log', 'itemid'=>$quickmail_log_record->id, 'filepath'=>$filepath, 'filename'=>$filename,
					'timecreated'=>$coursefile->get_timecreated(), 'timemodified'=>$coursefile->get_timemodified());
			if(!$fs->file_exists($coursecontext->id, 'block_quickmail', 'attachment_log', 0, $filepath, $filename)){
				$fs->create_file_from_storedfile($file_record, $coursefile->get_id());
			}
		}
	}
}

/*
 * Migrate all v1 DB data to v2 format
 */
function migrate_quickmail_v1_to_v2() {

	global $DB;

	$now = time();

	$dbman = $DB->get_manager();
	
	/////////////////////////////////////////////////////////////////
	// 
	//  CREATE SOME TEMP TABLES TO ASSIST IN MIGRATION (will be dropped later, promise)
	// 
	/////////////////////////////////////////////////////////////////

	foreach (['log', 'draft'] as $table_type) {
		$table = new xmldb_table(get_temp_table_name($table_type));

		$field = new xmldb_field('id');
	    $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
	    $table->addField($field);

	    $field = new xmldb_field('old_id');
	    $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
	    $table->addField($field);

	    $field = new xmldb_field('new_id');
	    $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, 0);
	    $table->addField($field);

	    $field = new xmldb_field('type');
	    $field->set_attributes(XMLDB_TYPE_CHAR, '8', null, XMLDB_NOTNULL);
	    $table->addField($field);

	    $field = new xmldb_field('message_created');
	    $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, 0);
	    $table->addField($field);

	    $field = new xmldb_field('recips_created');
	    $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, 0);
	    $table->addField($field);

	    $field = new xmldb_field('add_emails_created');
	    $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, 0);
	    $table->addField($field);

	    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

	    if ( ! $dbman->table_exists($table)) {
	        $dbman->create_table($table);
	    }
	}

	// COMMENTING ALTS OUT FOR NOW UNTIL A SOLUTION IS FOUND TO FIND THE USER WHO CREATED THE RECORD...
	// TODO: EMAIL ALT EMAILS TO RE-REGISTER???

	// $table = new xmldb_table(get_temp_alt_table_name());

	// $field = new xmldb_field('id');
	// $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
	// $table->addField($field);

	// $field = new xmldb_field('old_id');
	// $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
	// $table->addField($field);

	// $field = new xmldb_field('new_id');
	// $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, 0);
	// $table->addField($field);

	// $field = new xmldb_field('alt_created');
	// $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, 0);
	// $table->addField($field);

	// $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

	// if ( ! $dbman->table_exists($table)) {
	// 	$dbman->create_table($table);
	// }

    /////////////////////////////////////////////////////////////////
	// 
	//  POPULATE TEMP TABLES WITH NECESSARY DATA
	// 
	/////////////////////////////////////////////////////////////////

	foreach (['log', 'draft'] as $table_type) {

		// grab old record data from the appropriate table
		$olds = $DB->get_records(get_old_table_name($table_type), null, '', 'id,courseid');

		// insert a new temp record for each log/draft
		foreach ($olds as $old) {
			$temp_record = (object) [
				'old_id' => $old->id,
				'type' => $old->courseid == SITEID ? 'admin' : 'standard' // this is an assumption!
			];

			$DB->insert_record(get_temp_table_name($table_type), $temp_record);
		}
	}

	unset($olds);

	// COMMENTING OUT ALT MIGRATION FOR NOW, SEE NOTE ABOVE...

	// grab old record data from the appropriate table
	// $olds = $DB->get_records(get_old_alt_email_table_name(), null, '', 'id');

	// // insert a new temp record for each alt
	// foreach ($olds as $old) {
	// 	$temp_record = (object) [
	// 		'old_id' => $old->id,
	// 	];

	// 	$DB->insert_record(get_temp_alt_table_name(), $temp_record);
	// }

	// unset($olds);

	/////////////////////////////////////////////////////////////////
	// 
	//  MIGRATE SIGNATURE DATA
	// 
	/////////////////////////////////////////////////////////////////
	
	// get all current signatures
	$signatures = $DB->get_records(get_signature_table_name());

	// update each record's "persistent" details
	foreach ($signatures as $signature) {
		$signature->usermodified = $signature->user_id;
		$signature->timecreated = $now;
		$signature->timemodified = $now;

		$DB->update_record(get_signature_table_name(), $signature);
	}

	unset($signatures);

	/////////////////////////////////////////////////////////////////
	// 
	//  MIGRATE ALTERNATE EMAIL DATA
	//  
	//  NOTE: COMMENTING OUT ALT MIGRATION FOR NOW, SEE NOTE ABOVE...
	// 
	/////////////////////////////////////////////////////////////////
	
	// // while we can fetch a temp alt record that has not created a new record
	// while ($temp = $DB->get_record_select(get_temp_alt_table_name(), "alt_created = 0", [], '*', IGNORE_MULTIPLE)) {
	// 	// fetch the corresponding old record
	// 	$old = $DB->get_record(get_old_alt_email_table_name(), ['id' => $temp->old_id], '*', IGNORE_MULTIPLE);

	// 	// create a new record
	// 	$alt = (object) [
	// 		'setup_user_id' => 0,
	// 		'course_id' => $old->courseid,
	// 		'user_id' => 0,
	// 		'email' => $old->address,
	// 		'firstname' => '',
	// 		'lastname' => '',
	// 		'is_validated' => $old->valid,
	// 		'usermodified' => 0,
	// 		'timecreated' => $now,
	// 		'timemodified' => $now,
	// 		'timedeleted' => 0
	// 	];

	// 	// insert record, grabbing new id
	// 	$alt_id = $DB->insert_record(get_alt_email_table_name(), $alt);

	// 	// update the temp record to reflect that alt has been created
	// 	$temp->new_id = $alt_id;
	// 	$temp->alt_created = 1;

	// 	$DB->update_record(get_temp_alt_table_name(), $temp);
	// }

	/////////////////////////////////////////////////////////////////
	// 
	//  BEGIN PROCESS OF MIGRATING MESSAGES (BOTH MESSAGE TYPES)
	// 
	/////////////////////////////////////////////////////////////////

	foreach (['log', 'draft'] as $table_type) {
		// iterate through each "message type"
		foreach (['standard', 'admin'] as $type) {
			// while we can fetch a temp message record for this specific type
			while ($temp = $DB->get_record_select(get_temp_table_name($table_type), "type = :type AND message_created = 0", ['type' => $type], '*', IGNORE_MULTIPLE)) {
				// fetch the corresponding old record
				$old = $DB->get_record(get_old_table_name($table_type), ['id' => $temp->old_id], '*', IGNORE_MULTIPLE);

				// create a new record
				$message = (object) [
					'course_id' => $old->courseid,
					'user_id' => $old->userid,
					'message_type' => 'email',
					'subject' => $old->subject,
					'body' => $old->message,
					'editor_format' => $old->format,
					'sent_at' => $table_type == 'draft' ? 0 : $old->time,
					'to_send_at' => 0,
					'is_draft' => $table_type == 'draft' ? 1 : 0,
					'usermodified' => $old->userid,
					'timecreated' => $old->time,
					'timemodified' => $old->time,
					'timedeleted' => 0
				];

				// insert record, grabbing new id
				$message_id = $DB->insert_record(get_message_table_name(), $message);

				// update the temp record to reflect that message has been created
				$temp->new_id = $message_id;
				$temp->message_created = 1;

				$DB->update_record(get_temp_table_name($table_type), $temp);
			}
		}
	}

	/////////////////////////////////////////////////////////////////
	// 
	//  BEGIN PROCESS OF MIGRATING "STANDARD" MESSAGE RECIPIENTS (NOT ADMIN MESSAGES)
	// 
	/////////////////////////////////////////////////////////////////

	// while we can fetch a temp "standard message" record for this specific type that has not had recipients created
	while ($temp = $DB->get_record_select(get_temp_table_name('log'), "type = :type AND message_created = 1 AND recips_created = 0", ['type' => 'standard'], '*', IGNORE_MULTIPLE)) {
		// fetch the corresponding log record
		$log = $DB->get_record(get_old_table_name('log'), ['id' => $temp->old_id], '*', IGNORE_MULTIPLE);

		// fetch the new message record
		$message = $DB->get_record(get_message_table_name(), ['id' => $temp->new_id], '*', IGNORE_MULTIPLE);

		// iterate over all "mailtos" (user ids)
		foreach (explode(',', $log->mailto) as $user_id) {
			// create a new record
			$recipient = (object) [
				'message_id' => $message->id,
				'user_id' => $user_id,
				'sent_at' => $message->sent_at,
				'moodle_message_id' => 0,
				'usermodified' => $message->user_id,
				'timecreated' => $message->sent_at,
				'timemodified' => $message->sent_at
			];

			// insert record
			$DB->insert_record(get_message_recips_table_name(), $recipient, false);

			// update the temp record to reflect that message recipients have been created
			$temp->recips_created = 1;

			$DB->update_record(get_temp_table_name('log'), $temp);
		}
	}

	/////////////////////////////////////////////////////////////////
	// 
	//  BEGIN PROCESS OF MIGRATING "STANDARD" DRAFT RECIPIENTS (NOT ADMIN MESSAGES)
	// 
	/////////////////////////////////////////////////////////////////

	// while we can fetch a temp "standard message" record for this specific type that has not had recipients created
	while ($temp = $DB->get_record_select(get_temp_table_name('draft'), "type = :type AND message_created = 1 AND recips_created = 0", ['type' => 'standard'], '*', IGNORE_MULTIPLE)) {
		// fetch the corresponding draft record
		$draft = $DB->get_record(get_old_table_name('draft'), ['id' => $temp->old_id], '*', IGNORE_MULTIPLE);

		// fetch the new message record
		$message = $DB->get_record(get_message_table_name(), ['id' => $temp->new_id], '*', IGNORE_MULTIPLE);

		// iterate over all "mailtos" (user ids)
		foreach (explode(',', $draft->mailto) as $user_id) {
			// create a new record
			$recipient = (object) [
				'message_id' => $message->id,
				'type' => 'include',
				'recipient_type' => 'user',
				'recipient_id' => $user_id,
				'timecreated' => $message->timecreated,
				'timemodified' => $message->timemodified
			];

			// insert record
			$DB->insert_record(get_draft_recips_table_name(), $recipient, false);
		}
		
		// update the temp record to reflect that message recipients have been created
		$temp->recips_created = 1;

		$DB->update_record(get_temp_table_name('draft'), $temp);
	}

	/////////////////////////////////////////////////////////////////
	// 
	//  BEGIN PROCESS OF MIGRATING "ADMIN" DRAFT RECIPIENTS (NOT STANDARD MESSAGES)
	// 
	/////////////////////////////////////////////////////////////////

	// while we can fetch a temp "admin message" record for this specific type that has not had recipients created
	while ($temp = $DB->get_record_select(get_temp_table_name('draft'), "type = :type AND message_created = 1 AND recips_created = 0", ['type' => 'admin'], '*', IGNORE_MULTIPLE)) {
		// fetch the corresponding draft record
		$draft = $DB->get_record(get_old_table_name('draft'), ['id' => $temp->old_id], '*', IGNORE_MULTIPLE);

		// fetch the new message record
		$message = $DB->get_record(get_message_table_name(), ['id' => $temp->new_id], '*', IGNORE_MULTIPLE);

		// create a new record using the serialized "mailto"
		$recipient = (object) [
			'message_id' => $message->id,
			'type' => 'include',
			'recipient_type' => 'filter',
			'recipient_filter' => $draft->mailto,
			'timecreated' => $message->timecreated,
			'timemodified' => $message->timemodified
		];
		
		// insert record
		$DB->insert_record(get_draft_recips_table_name(), $recipient, false);
		
		// update the temp record to reflect that message recipients have been created
		$temp->recips_created = 1;

		$DB->update_record(get_temp_table_name('draft'), $temp);
	}

	/////////////////////////////////////////////////////////////////
	// 
	//  BEGIN PROCESS OF MIGRATING ADDITIONAL EMAIL RECIPIENTS (BOTH MESSAGE TYPES)
	// 
	/////////////////////////////////////////////////////////////////

	foreach (['log', 'draft'] as $table_type) {
		// iterate through each "message type"
		foreach (['standard', 'admin'] as $type) {
			// while we can fetch a temp "standard message" record for this specific type
			while ($temp = $DB->get_record_select(get_temp_table_name($table_type), "type = :type AND message_created = 1 AND recips_created = 1 AND add_emails_created = 0", ['type' => $type], '*', IGNORE_MULTIPLE)) {
				// fetch the corresponding old record
				$old = $DB->get_record(get_old_table_name($table_type), ['id' => $temp->old_id], '*', IGNORE_MULTIPLE);

				// fetch the new message record
				$message = $DB->get_record(get_message_table_name(), ['id' => $temp->new_id], '*', IGNORE_MULTIPLE);

				// if the old record had additional emails
				if ( ! empty($old->additional_emails)) {
					// iterate over each email
					foreach (explode(',', $old->additional_emails) as $email) {
						// create a new record
						$add_email = (object) [
							'message_id' => $message->id,
							'email' => trim($email),
							'sent_at' => $message->sent_at,
							'usermodified' => $message->user_id,
							'timecreated' => $message->timecreated,
							'timemodified' => $message->timemodified
						];

						// insert record
						$DB->insert_record(get_add_emails_table_name(), $add_email, false);
					}
				}
				
				// update the temp record to reflect that message recipients have been created
				$temp->add_emails_created = 1;

				$DB->update_record(get_temp_table_name($table_type), $temp);
			}
		}
	}

	/////////////////////////////////////////////////////////////////
	// 
	//  DROP THE TEMP TABLES, AS PROMISED
	// 
	/////////////////////////////////////////////////////////////////

	foreach (['log', 'draft'] as $table_type) {
		$table = new xmldb_table(get_temp_table_name($table_type));

		if ($dbman->table_exists($table)) {
	        $dbman->drop_table($table);
	    }
	}

	// $table = new xmldb_table(get_temp_alt_table_name());

	// if ($dbman->table_exists($table)) {
 //        $dbman->drop_table($table);
 //    }

	// all done!
	return true;
}

function get_old_table_name($table_type) {
	return $table_type == 'draft'
		? 'block_quickmail_drafts'
		: 'block_quickmail_log';
}

function get_temp_table_name($table_type) {
	return $table_type == 'draft'
		? 'block_quickmail_temp_draft_m'
		: 'block_quickmail_temp_log_m';
}

function get_temp_alt_table_name() {
	return 'block_quickmail_temp_alt_m';
}

function get_signature_table_name() {
	return 'block_quickmail_signatures';
}

function get_old_alt_email_table_name() {
	return 'block_quickmail_alternate';
}

function get_alt_email_table_name() {
	return 'block_quickmail_alt_emails';
}

function get_message_table_name() {
	return 'block_quickmail_messages';
}

function get_message_recips_table_name() {
	return 'block_quickmail_msg_recips';
}

function get_draft_recips_table_name() {
	return 'block_quickmail_draft_recips';
}

function get_add_emails_table_name() {
	return 'block_quickmail_msg_ad_email';
}