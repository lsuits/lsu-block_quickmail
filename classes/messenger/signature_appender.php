<?php

namespace block_quickmail\messenger;

use block_quickmail\persistents\signature;

class signature_appender {

    public $body;
    public $user_id;
    public $signature_id;

    /**
     * Construct the message signature appender
     * 
     * @param string  $body           the message body
     * @param int     $user_id        the user id of the user sending the message
     * @param int     $signature_id   the signature id to be appended
     */
    public function __construct($body, $user_id, $signature_id = 0) {
        $this->body = $body;
        $this->user_id = $user_id;
        $this->signature_id = $signature_id;
    }

    public static function append_user_signature_to_body($body, $user_id, $signature_id = 0)
    {
        $appender = new self($body, $user_id, $signature_id);

        return $appender->get_signature_appended_body();
    }

    public function get_signature_appended_body()
    {
        if ( ! $this->signature_id) {
            return $this->body;
        }

        if ( ! $signature = signature::find_user_signature_or_null($this->signature_id, $this->user_id)) {
            return $this->body;
        }

        $this->body = $this->body . '<br><br>' . $signature->get('signature');

        return $this->body;
    }

}