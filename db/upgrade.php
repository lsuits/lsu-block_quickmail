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
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_block_quickmail_upgrade($oldversion) {
    global $DB;

    $result = true;

    $dbman = $DB->get_manager();

    // 1.9 to 2.0 upgrade
    if ($oldversion < 2011021812) {
        // Changing type of field attachment on table block_quickmail_log to text
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('attachment', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, 'message');

        // Launch change of type for field attachment
        $dbman->change_field_type($table, $field);

        // Rename field timesent on table block_quickmail_log to time
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('timesent', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'format');

        // Conditionally launch rename field timesent
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'time');
        }

        // Define table block_quickmail_signatures to be created
        $table = new xmldb_table('block_quickmail_signatures');

        // Adding fields to table block_quickmail_signatures
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('title', XMLDB_TYPE_CHAR, '125', null, null, null, null);
        $table->add_field('signature', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('default_flag', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');

        // Adding keys to table block_quickmail_signatures
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_quickmail_signatures
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_quickmail_drafts to be created
        $table = new xmldb_table('block_quickmail_drafts');

        // Adding fields to table block_quickmail_drafts
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('mailto', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('subject', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('attachment', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null);
        $table->add_field('format', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);

        // Adding keys to table block_quickmail_drafts
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_quickmail_drafts
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_quickmail_config to be created
        $table = new xmldb_table('block_quickmail_config');

        // Adding fields to table block_quickmail_config
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('coursesid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '25', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_CHAR, '125', null, null, null, null);

        // Adding keys to table block_quickmail_config
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_quickmail_config
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // quickmail savepoint reached
        upgrade_block_savepoint($result, 2011021812, 'quickmail');
    }

    if ($oldversion < 2012021014) {
        $table = new xmldb_table('block_quickmail_alternate');

        $field = new xmldb_field('id');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, true, null, null);

        $table->addField($field);

        $field = new xmldb_field('courseid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, false, null, 'id');

        $table->addField($field);

        $field = new xmldb_field('address');
        $field->set_attributes(XMLDB_TYPE_CHAR, '100', null,
            XMLDB_NOTNULL, false, null, 'courseid');

        $table->addField($field);

        $field = new xmldb_field('valid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, false, '0', 'address');

        $table->addField($field);

        $key = new xmldb_key('PRIMARY');
        $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));

        $table->addKey($key);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        foreach (array('log', 'drafts') as $table) {
            // Define field alternateid to be added to block_quickmail_log
            $table = new xmldb_table('block_quickmail_' . $table);
            $field = new xmldb_field('alternateid', XMLDB_TYPE_INTEGER, '10',
                XMLDB_UNSIGNED, null, null, null, 'userid');

            // Conditionally launch add field alternateid
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // quickmail savepoint reached
        upgrade_block_savepoint($result, 2012021014, 'quickmail');
    }

    if ($oldversion < 2012061112) {
        // Restructure database references to the new filearea locations
        foreach (array('log', 'drafts') as $type) {
            $params = array(
                'component' => 'block_quickmail_' . $type,
                'filearea' => 'attachment'
            );

            $attachments = $DB->get_records('files', $params);

            foreach ($attachments as $attachment) {
                $attachment->filearea = 'attachment_' . $type;
                $attachment->component = 'block_quickmail';

                $result = $result && $DB->update_record('files', $attachment);
            }
        }

        upgrade_block_savepoint($result, 2012061112, 'quickmail');
    }
    
    if ($oldversion < 2012061112) {
    	migrate_quickmail_20();
    }
    
    if ($oldversion < 2014042914){

         // Define field status to be dropped from block_quickmail_log.
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('status');

        // Conditionally launch drop field status.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field status to be added to block_quickmail_log.
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('failuserids', XMLDB_TYPE_TEXT, null, null, null, null, null, 'time');
        $field2 = new xmldb_field('additional_emails', XMLDB_TYPE_TEXT, null, null, null, null, null, 'failuserids');
        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
	   
       if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        
        // Define field additional_emails to be added to block_quickmail_drafts.
        $table = new xmldb_table('block_quickmail_drafts');
        $field = new xmldb_field('additional_emails', XMLDB_TYPE_TEXT, null, null, null, null, null, 'time');

        // Conditionally launch add field additional_emails.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Quickmail savepoint reached.
        upgrade_block_savepoint(true, 2014042914, 'quickmail');
    }

    // upgrade schema for version 2.0

    if ($oldversion < 2017091400) {

        // Define table block_quickmail_messages to be created.
        $table = new xmldb_table('block_quickmail_messages');

        // Adding fields to table block_quickmail_messages.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('alternate_email_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('signature_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('subject', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('body', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('editor_format', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');
        $table->add_field('sent_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);
        $table->add_field('is_draft', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_quickmail_messages.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('course_id', XMLDB_KEY_FOREIGN, array('course_id'), 'course', array('id'));
        $table->add_key('user_id', XMLDB_KEY_FOREIGN, array('user_id'), 'user', array('id'));
        $table->add_key('alternate_email_id', XMLDB_KEY_FOREIGN, array('alternate_email_id'), 'block_quickmail_alt_emails', array('id'));
        $table->add_key('signature_id', XMLDB_KEY_FOREIGN, array('signature_id'), 'block_quickmail_signatures', array('id'));

        // Conditionally launch create table for block_quickmail_messages.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_quickmail_msg_recips to be created.
        $table = new xmldb_table('block_quickmail_msg_recips');

        // Adding fields to table block_quickmail_msg_recips.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('message_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('sent_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_quickmail_msg_recips.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('message_id', XMLDB_KEY_FOREIGN, array('message_id'), 'block_quickmail_messages', array('id'));
        $table->add_key('user_id', XMLDB_KEY_FOREIGN, array('user_id'), 'user', array('id'));

        // Conditionally launch create table for block_quickmail_msg_recips.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_quickmail_msg_ad_email to be created.
        $table = new xmldb_table('block_quickmail_msg_ad_email');

        // Adding fields to table block_quickmail_msg_ad_email.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('message_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sent_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_quickmail_msg_ad_email.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('message_id', XMLDB_KEY_FOREIGN, array('message_id'), 'block_quickmail_messages', array('id'));

        // Conditionally launch create table for block_quickmail_msg_ad_email.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_quickmail_msg_attach to be created.
        $table = new xmldb_table('block_quickmail_msg_attach');

        // Adding fields to table block_quickmail_msg_attach.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('message_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('filename', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_quickmail_msg_attach.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('message_id', XMLDB_KEY_FOREIGN, array('message_id'), 'block_quickmail_messages', array('id'));

        // Conditionally launch create table for block_quickmail_msg_attach.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_quickmail_alt_emails to be created.
        $table = new xmldb_table('block_quickmail_alt_emails');

        // Adding fields to table block_quickmail_alt_emails.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('setup_user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('course_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('email', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('is_validated', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_quickmail_alt_emails.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('setup_user_id', XMLDB_KEY_FOREIGN, array('setup_user_id'), 'user', array('id'));
        $table->add_key('course_id', XMLDB_KEY_FOREIGN, array('course_id'), 'course', array('id'));
        $table->add_key('user_id', XMLDB_KEY_FOREIGN, array('user_id'), 'user', array('id'));

        // Conditionally launch create table for block_quickmail_alt_emails.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Quickmail savepoint reached.
        upgrade_block_savepoint(true, 2017091400, 'quickmail');
    }

    if ($oldversion < 2017091800) {
        // add fields and change a field name in block_quickmail_signatures table
        $signature_table = new xmldb_table('block_quickmail_signatures');
        
        // renaming userid to user_id for consistency in new version
        $user_id_field = new xmldb_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        // adding required fields for persistent api
        $usermodified_field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $timecreated_field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $timemodified_field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Conditionally launch add field user_id.
        if (!$dbman->field_exists($signature_table, $user_id_field)) {
            $dbman->add_field($signature_table, $user_id_field);
        }

        // Conditionally launch add field usermodified.
        if (!$dbman->field_exists($signature_table, $usermodified_field)) {
            $dbman->add_field($signature_table, $usermodified_field);
        }

        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($signature_table, $timecreated_field)) {
            $dbman->add_field($signature_table, $timecreated_field);
        }

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($signature_table, $timemodified_field)) {
            $dbman->add_field($signature_table, $timemodified_field);
        }

        $user_id_key = new xmldb_key('user_id', XMLDB_KEY_FOREIGN, array('user_id'), 'user', array('id'));

        // Launch add key primary.
        $dbman->add_key($signature_table, $user_id_key);

        // rename userid => user_id for consistency
        // $userid_field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        // $dbman->rename_field($signature_table, $userid_field, 'user_id');

        // make title non null
        $title_field = new xmldb_field('title', XMLDB_TYPE_CHAR, '125', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_notnull($signature_table, $title_field);

        // make signature non null
        $signature_field = new xmldb_field('signature', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_notnull($signature_table, $signature_field);

        // change default on default_flag to 0 (will be handled during creation/update)
        $default_flag_field = new xmldb_field('default_flag', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $dbman->change_field_default($signature_table, $default_flag_field);

        /*
         * Update signature records, update all userid => user_id
         */
        $all_signatures = $DB->get_records('block_quickmail_signatures');

        foreach ($all_signatures as $signature) {
            $signature->user_id = $signature->userid;

            $DB->update_record('block_quickmail_signatures', $signature);
        }

        $userid_field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        $dbman->drop_field($signature_table, $userid_field);

        // Quickmail savepoint reached.
        upgrade_block_savepoint(true, 2017091800, 'quickmail');
    }

    // add soft deletes to a few tables
    if ($oldversion < 2017101100) {
        foreach (['block_quickmail_messages', 'block_quickmail_alt_emails', 'block_quickmail_signatures'] as $table_name) {
            // get the table
            $table = new xmldb_table($table_name);

            // define the field
            $field = new xmldb_field('timedeleted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

            // Conditionally launch add field additional_emails.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
    }

    return $result;
}