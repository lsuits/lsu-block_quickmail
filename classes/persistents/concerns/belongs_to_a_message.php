<?php

namespace block_quickmail\persistents\concerns;

use block_quickmail\persistents\message;

trait belongs_to_a_message {

    ///////////////////////////////////////////////
    ///
    ///  RELATIONSHIPS
    /// 
    ///////////////////////////////////////////////

    /**
     * Returns the parent message object of this message recipient.
     *
     * @return stdClass
     */
    public function get_message() {
        return message::find_or_null($this->get('message_id'));
    }

    ///////////////////////////////////////////////
    ///
    ///  VALIDATORS
    /// 
    ///////////////////////////////////////////////

    /**
     * Validate the message ID.
     *
     * @param int $value The value.
     * @return true|string
     */
    protected function validate_message_id($value) {
        if ( ! $message = message::find_or_null($value)) {
            // @TODO - make a lang string for this!
            // return new lang_string('invalidcourseid', 'error');
            return 'no message record!';
        }

        return true;
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Creates a new persistent record for the given message with the given array of attributes
     * 
     * @param  block_quickmail\persistents\message  $message
     * @param  array  $params  [attr => value]
     * @return object (persistent)
     * @throws dml_missing_record_exception
     */
    public static function create_for_message(message $message, array $params)
    {
        // merge the message id into the creation parameters
        $params = array_merge(['message_id' => $message->get('id')], $params);

        return self::create_new($params);
    }

}