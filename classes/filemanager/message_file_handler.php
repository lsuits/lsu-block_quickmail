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

namespace block_quickmail\filemanager;

use block_quickmail_config;
use block_quickmail_cache;
use block_quickmail\persistents\message;
use block_quickmail\persistents\message_attachment;
use context_course;

class message_file_handler {

    public static $plugin_name = 'block_quickmail';

    public $message;
    public $course;
    public $context;
    public $file_storage;
    public $uploaded_files;

    public function __construct(message $message) {
        $this->message = $message;
        $this->course = $message->get_course();
        $this->context = $this->get_context();
        $this->file_storage = get_file_storage();
        $this->uploaded_files = [];
    }

    /**
     * Executes posted file attachments for the given message
     * 
     * @param  message  $message
     * @param  object   $form_data   mform post data
     * @param  string   $filearea    "attachments"
     * @return void
     */
    public static function handle_posted_attachments($message, $form_data, $filearea)
    {
        $file_handler = new self($message);

        // store the filearea's files within moodle
        $file_handler->store_posted_filearea($form_data, $filearea);

        // update this message's list of file attachments
        $file_handler->sync_attachments();
    }

    /**
     * Zips all of the file attachments for the given message and makes available for the given user,
     * Returns the path in which the temp files are stored
     * 
     * @param  message  $message
     * @param  object  $user      moodle user
     * @param  string  $filename   file name to name the temp zip file
     * @return string  path to the generated file
     */
    public static function zip_attachments_for_user($message, $user, $filename = 'attachments.zip') {
        global $CFG;

        $path = $CFG->tempdir . '/' . self::$plugin_name . '/' . $user->id;

        if ( ! file_exists($path)) {
            mkdir($path, $CFG->directorypermissions, true);
        }

        $zip_filename = $path . '/' . $filename;

        $course = $message->get_course();

        $context = context_course::instance($course->id);

        $fs = get_file_storage();
        $packer = get_file_packer();

        $files = $fs->get_area_files(
            $context->id,
            self::$plugin_name,
            'attachments',
            $message->get('id'),
            true
        );

        $stored_files = [];
        
        // iterate through each of the file records
        foreach ($files as $file) {
            // if the record is a directory, skip
            if ($file->is_directory() and $file->get_filename() == '.') {
                continue;
            }

            // add the file references to the stack
            $stored_files[$file->get_filepath() . $file->get_filename()] = $file;
        }

        // zip the files
        $packer->archive_to_pathname($stored_files, $zip_filename);

        return $zip_filename;
    }

    /**
     * Stores and renames the given filearea's files from the given posted data
     * 
     * @param  object  $form_data  mform post data
     * @param  string  $filearea  "attachments"
     * @return void
     */
    private function store_posted_filearea($form_data, $filearea)
    {
        if (empty($form_data->attachments)) {
            return;
        }

        // move the files from "user draft" to this filearea
        file_save_draft_area_files(
            $form_data->$filearea, 
            $this->context->id, 
            self::$plugin_name,
            $filearea,
            $this->message->get('id'), 
            block_quickmail_config::get_filemanager_options()
        );

        // iterate through each uploaded file
        foreach ($this->fetch_uploaded_file_data($filearea) as $file) {
            // add its data to the stack
            $this->add_to_uploaded_files($filearea, $file->filepath, $file->filename);
        }
    }

    /**
     * Replaces all existing message_attachment records for this message with the given uploaded file data
     * 
     * @param  array  $uploaded_files
     * @return void
     */
    private function sync_attachments($uploaded_files = [])
    {
        // clear all current attachment records
        message_attachment::clear_all_for_message($this->message);

        // get uploaded attachment files from the stack, if any
        $uploaded_files = $this->get_uploaded_files('attachments');

        $count = 0;

        // iterate through each file
        foreach ($uploaded_files as $file) {
            // if any exceptions, proceed gracefully to the next
            try {
                message_attachment::create_for_message($this->message, [
                    'path' => $file['path'],
                    'filename' => $file['filename'],
                ]);

                $count++;
            } catch (\Exception $e) {
                // most likely invalid user, exception thrown due to validation error
                // log this?
                continue;
            }
        }

        // cache the count for external use
        block_quickmail_cache::store('qm_msg_attach_count')->put($this->message->get('id'), $count);
    }

    /**
     * Returns all of the uploaded file records of the given filearea for this message
     * 
     * @param  string  $filearea  "attachments"
     * @return array
     */
    private function fetch_uploaded_file_data($filearea)
    {
        global $DB;

        $files = $DB->get_records_sql('SELECT * FROM {files} WHERE component = ? AND filearea = ? AND itemid = ? AND filename <> ?', [
            self::$plugin_name, 
            $filearea, 
            $this->message->get('id'), 
            '.'
        ]);

        return $files;
    }

    /**
     * Adds the given path and filename to the given filearea's uploaded file array
     * 
     * @param string  $filearea  "attachments"
     * @param string  $path      file path
     * @param string  $filename
     * @return  void
     */
    private function add_to_uploaded_files($filearea, $path, $filename)
    {
        $this->uploaded_files[$filearea][] = [
            'path' => $path,
            'filename' => $filename,
        ];
    }

    /**
     * Returns all of the set uploaded file data for the given filearea
     * 
     * @param string  $filearea  "attachments"
     * @return array
     */
    private function get_uploaded_files($filearea)
    {
        return ! array_key_exists($filearea, $this->uploaded_files)
            ? []
            : $this->uploaded_files[$filearea];
    }

    /**
     * Returns this handler's course context
     * 
     * @return object
     */
    private function get_context()
    {
        return context_course::instance($this->course->id);
    }

}