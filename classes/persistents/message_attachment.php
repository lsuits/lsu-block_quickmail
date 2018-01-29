<?php

namespace block_quickmail\persistents;

use core\persistent;
use block_quickmail\persistents\message;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\belongs_to_a_message;
 
class message_attachment extends persistent {
 
    use enhanced_persistent, 
        belongs_to_a_message;

    /** Table name for the persistent. */
    const TABLE = 'block_quickmail_msg_attach';
 
    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'message_id' => [
                'type' => PARAM_INT,
            ],
            'path' => [
                'type' => PARAM_TEXT,
            ],
            'filename' => [
                'type' => PARAM_FILE,
            ],
        ];
    }
 
    ///////////////////////////////////////////////
    ///
    ///  RELATIONSHIPS
    /// 
    ///////////////////////////////////////////////

    //

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
     * Returns this attachment's full file path (path + filename)
     * 
     * @param  bool  $leading_slash  whether or not to include a leading slash
     * @return string
     */
    public function get_full_filepath($leading_slash = false)
    {
        $path = $this->get('path') . $this->get('filename');

        return ! $leading_slash
            ? ltrim($path, '/')
            : $path;
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Deletes all attachment records for this message
     * 
     * @param  message $message
     * @return void
     */
    public static function clear_all_for_message(message $message)
    {
        global $DB;

        $DB->delete_records('block_quickmail_msg_attach', ['message_id' => $message->get('id')]);
    }
 
}