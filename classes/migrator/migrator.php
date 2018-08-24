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

namespace block_quickmail\migrator;

use block_quickmail\migrator\chunk_size_met_exception;
use \core\task\manager as task_manager;

class migrator {

    public $db;
    public $cfg;
    public $site_id;
    public $chunk_size;
    public $migrated_count;
    public static $old_drafts_table = 'block_quickmail_drafts';
    public static $old_log_table = 'block_quickmail_log';
    public static $message_table = 'block_quickmail_messages';
    public static $draft_recipient_table = 'block_quickmail_draft_recips';
    public static $message_recipient_table = 'block_quickmail_msg_recips';
    public static $additional_email_table = 'block_quickmail_msg_ad_email';

    public function __construct() {
        global $DB;
        global $CFG;

        $this->db = $DB;
        $this->cfg = $CFG;
        $this->site_id = SITEID;
        $this->chunk_size = $this->get_configured_chunk_size();
        $this->migrated_count = 0;
    }

    /**
     * Reports whether or not the migration task is enabled
     * 
     * @return bool
     */
    public static function is_enabled()
    {
        $task = task_manager::get_scheduled_task('block_quickmail\tasks\migrate_legacy_data_task');

        return ! $task->get_disabled();
    }

    /**
     * Reports whether or not Quickmail's legacy tables exist
     * 
     * @return bool
     */
    public static function old_tables_exist()
    {
        global $DB;
        
        $dbman = $DB->get_manager();
        
        return $dbman->table_exists(self::$old_drafts_table) || $dbman->table_exists(self::$old_log_table);
    }

    /**
     * Drops old tables
     * 
     * @return void
     */
    public static function drop_old_tables()
    {
        global $DB;
        
        $dbman = $DB->get_manager();

        $drafts_table = new \xmldb_table(self::$old_drafts_table);

        if ($dbman->table_exists($drafts_table)) {
            $dbman->drop_table($drafts_table);
        }

        $logs_table = new \xmldb_table(self::$old_log_table);

        if ($dbman->table_exists($logs_table)) {
            $dbman->drop_table($logs_table);
        }
    }

    /**
     * Returns a count of all old records of a given type
     * 
     * @param  string  $type  drafts|log
     * @return int
     */
    public static function total_count($type)
    {
        $migrator = new self();

        return $migrator->get_total_count($type);
    }

    /**
     * Returns a count of all migrated records of a given type
     * 
     * @param  string  $type  drafts|log
     * @return int
     */
    public static function migrated_count($type)
    {
        $migrator = new self();

        return $migrator->get_migrated_count($type);
    }

    /**
     * Executes migration of any historical Quickmail data from old format to new, adhering to a configurable number of
     * transactions before stopping. Priority is given to course-level emails (not admin messages) with drafts first and 
     * then sent messages second. After all course-level emails are complete, this process will move on to site-level
     * messages in the same fashion.
     *
     * NOTE: this process does not convert old email attachment data, alternate email, or signature data
     * 
     * @return bool  whether or not the migration process has completed
     * @throws block_quickmail\migrator\chunk_size_met_exception
     * @throws \Exception  a catch all in case anything unexpected happens
     */
    public static function execute()
    {
        $migrator = new self();

        // course drafts
        $migrator->migrate(true, false);
        // course sents
        $migrator->migrate(false, false);
        // site drafts
        $migrator->migrate(true, true);
        // site sents
        $migrator->migrate(false, true);

        return true;
    }

    /**
     * Executes migration of historic data from old tables to new by creating messages with recipients and any additional emails
     * 
     * @param  bool    $is_draft
     * @param  bool    $is_admin_message  whether or not this process should migrate course-level or site-level emails
     * @return void
     * @throws chunk_size_met_exception
     */
    public function migrate($is_draft, $is_admin_message)
    {
        if ( ! empty($this->chunk_size)) {
            // while we can pull an unmigrated message of the given status type (beginning with latest)
            while ($record = $this->find_latest_unmigrated($is_draft, $is_admin_message)) {
                $this->create_message($is_draft, $is_admin_message, $record);

                $this->mark_old_record_as_migrated($is_draft, $record);

                $this->migrated_count++;

                $this->check_chunk_size();
            }
        }
    }

    /**
     * Creates a new message record for the given old record
     * 
     * @param  bool      $is_draft
     * @param  bool      $is_admin_message
     * @param  stdClass  $old_record
     * @return void
     */
    private function create_message($is_draft, $is_admin_message, $old_record)
    {
        // construct a new message record
        $message = (object) [
            'course_id' => $old_record->courseid,
            'user_id' => $old_record->userid,
            'message_type' => 'email',
            'subject' => $old_record->subject,
            'body' => $old_record->message,
            'editor_format' => $old_record->format,
            'sent_at' => $is_draft ? 0 : $old_record->time,
            'to_send_at' => 0,
            'is_draft' => $is_draft ? 1 : 0,
            'usermodified' => $old_record->userid,
            'timecreated' => $old_record->time,
            'timemodified' => $old_record->time,
            'timedeleted' => 0
        ];

        // insert record as message, returning message id
        $message_id = $this->db->insert_record(self::$message_table, $message);
        
        // if original message had any recipients
        if ( ! empty($old_record->mailto)) {
            $this->create_recipients($is_draft, $is_admin_message, $message_id, $old_record);
        }

        // if original message had any additional emails
        if ( ! empty($old_record->additional_emails)) {
            $this->create_additional_emails_for_message($is_admin_message, $message_id, $old_record);
        }
    }

    /**
     * Creates new recipients for the given message id, depending on type
     * 
     * @param  bool      $is_draft
     * @param  bool      $is_admin_message
     * @param  int       $message_id
     * @param  stdClass  $old_record
     * @return void
     */
    private function create_recipients($is_draft, $is_admin_message, $message_id, $old_record)
    {
        if ($is_draft) {
            $this->create_draft_recipients($is_admin_message, $message_id, $old_record);
        } else {
            $this->create_message_recipients($is_admin_message, $message_id, $old_record);
        }
    }

    /**
     * Creates new draft recipients for the given message id
     * 
     * @param  bool      $is_admin_message
     * @param  int       $message_id
     * @param  stdClass  $old_record
     * @return void
     */
    private function create_draft_recipients($is_admin_message, $message_id, $old_record)
    {
        if ($is_admin_message) {
            $this->create_admin_draft_recipients($message_id, $old_record);
        } else {
            $this->create_course_draft_recipients($message_id, $old_record);
        }
    }

    /**
     * Creates new draft recipients for the given course-level message id
     * 
     * @param  int       $message_id
     * @param  stdClass  $old_record
     * @return void
     */
    private function create_course_draft_recipients($message_id, $old_record)
    {
        // for each mailto user
        foreach (explode(',', trim($old_record->mailto)) as $user_id) {
            // construct a new recipient record for this message
            $recipient = (object) [
                'message_id' => $message_id,
                'type' => 'include',
                'recipient_type' => 'user',
                'recipient_id' => $user_id,
                'usermodified' => $old_record->userid,
                'timecreated' => $old_record->time,
                'timemodified' => $old_record->time,
            ];

            $this->db->insert_record(self::$draft_recipient_table, $recipient);
        }
    }

    /**
     * Creates new draft recipients for the given site-level message id
     * 
     * @param  int       $message_id
     * @param  stdClass  $old_record
     * @return void
     */
    private function create_admin_draft_recipients($message_id, $old_record)
    {
        // construct a new recipient record for this message
        $recipient = (object) [
            'message_id' => $message_id,
            'type' => 'include',
            'recipient_type' => 'filter',
            'recipient_filter' => $old_record->mailto,
            'usermodified' => $old_record->userid,
            'timecreated' => $old_record->time,
            'timemodified' => $old_record->time,
        ];

        $this->db->insert_record(self::$draft_recipient_table, $recipient);
    }

    /**
     * Creates new message recipients for the given message id
     *
     * Note: this skips creating site-level message recipients to avoid having to use old filters
     * to lookup users
     * 
     * @param  bool      $is_admin_message
     * @param  int       $message_id
     * @param  stdClass  $old_record
     * @return void
     */
    private function create_message_recipients($is_admin_message, $message_id, $old_record)
    {
        if ( ! $is_admin_message) {
            // for each mailto user
            foreach (explode(',', trim($old_record->mailto)) as $user_id) {
                // create a new record
                $recipient = (object) [
                    'message_id' => $message_id,
                    'user_id' => $user_id,
                    'sent_at' => $old_record->time,
                    'moodle_message_id' => 0,
                    'usermodified' => $user_id,
                    'timecreated' => $old_record->time,
                    'timemodified' => $old_record->time
                ];

                $this->db->insert_record(self::$message_recipient_table, $recipient);
            }
        }
    }

    /**
     * Creates new additional email record for the given message id
     * 
     * @param  bool      $is_admin_message
     * @param  int       $message_id
     * @param  stdClass  $old_record
     * @return void
     */
    private function create_additional_emails_for_message($is_admin_message, $message_id, $old_record)
    {
        // sanitize additional email string
        $additional_email_string = str_replace(';', ',', $old_record->additional_emails);

        // for each additional email
        foreach (explode(',', trim($additional_email_string)) as $email) {
            // construct a new additional email record for this message
            $add_email = (object) [
                'message_id' => $message_id,
                'email' => trim($email),
                'sent_at' => $old_record->time,
                'usermodified' => $old_record->userid,
                'timecreated' => $old_record->time,
                'timemodified' => $old_record->time
            ];

            $this->db->insert_record(self::$additional_email_table, $add_email);
        }
    }

    /**
     * Returns the most recent unmigrated record of a given type, or null
     * 
     * @param  bool    $is_draft
     * @param  bool    $is_admin_message  if true, will return only a site-scoped message (not course)
     * @return stdClass|null
     */
    private function find_latest_unmigrated($is_draft, $is_admin_message = false)
    {
        $sql = 'select * from ' . $this->get_raw_source_table_name($is_draft) . ' where has_migrated = 0';

        $sql .= $is_admin_message
            ? ' and courseid = ' . $this->site_id
            : ' and courseid != ' . $this->site_id;

        $sql .= ' order by id desc limit 1;';

        try {
            return $this->db->get_record_sql($sql, null, MUST_EXIST);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Updates the given record to a "migrated" status, depending on type
     * 
     * @param  bool     $is_draft
     * @param  stdClass $old_record
     * @return void
     */
    private function mark_old_record_as_migrated($is_draft, $old_record)
    {
        $old_record->has_migrated = 1;
        
        $table_name = $is_draft
            ? self::$old_drafts_table
            : self::$old_log_table;

        $this->db->update_record($table_name, $old_record);
    }

    /**
     * Returns the full table name (including prefix) of the old source table depending on type of message
     * 
     * @param  bool   $is_draft
     * @return string
     */
    private function get_raw_source_table_name($is_draft)
    {
        $name = $is_draft
            ? self::$old_drafts_table
            : self::$old_log_table;

        return $this->cfg->prefix . $name;
    }

    /**
     * Throws an exception if this migrate execution has exceeded the maximum number of configured iterations
     * 
     * @return void
     * @throws chunk_size_met_exception
     */
    private function check_chunk_size()
    {
        if ($this->migrated_count >= $this->chunk_size) {
            throw new chunk_size_met_exception;
        }
    }

    /**
     * Returns count of given type of records
     * 
     * @param  string  $type  drafts|log
     * @return int
     */
    public function get_total_count($type)
    {
        return (int) $this->db->count_records('block_quickmail_' . $type);
    }

    /**
     * Returns count of given type of records that have been migrated
     * 
     * @param  string  $type  drafts|log
     * @return int
     */
    public function get_migrated_count($type)
    {
        return (int) $this->db->count_records('block_quickmail_' . $type, ['has_migrated' => 1]);
    }

    /**
     * Returns the configured chunk size amount, defaulting to 1000
     * 
     * @return int
     */
    private function get_configured_chunk_size()
    {
        // attempt to pull the configured chunk size and return
        if ($chunk_size = get_config('moodle', 'block_quickmail_migration_chunk_size')) {
            if (is_numeric($chunk_size)) {
                return (int) $chunk_size;
            }
        }

        // default
        return 1000;
    }

}