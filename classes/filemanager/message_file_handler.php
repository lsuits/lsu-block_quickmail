<?php

namespace block_quickmail\filemanager;

use block_quickmail_config;
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
     * Stores and renames the given filearea's files from the given posted data
     * 
     * @param  object  $form_data  mform post data
     * @param  string  $filearea  "attachments"
     * @return void
     */
    private function store_posted_filearea($form_data, $filearea)
    {
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

        // iterate through each file
        foreach ($uploaded_files as $file) {
            // if any exceptions, proceed gracefully to the next
            try {
                message_attachment::create_for_message($this->message, [
                    'path' => $file['path'],
                    'filename' => $file['filename'],
                ]);
            } catch (\Exception $e) {
                // most likely invalid user, exception thrown due to validation error
                // log this?
                continue;
            }
        }
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