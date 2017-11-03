<?php

namespace block_quickmail\messenger\exceptions;

class messenger_authentication_exception extends \Exception {
    
    public $message;

    public function __construct($message = 'Messenger authentication exception') {
        $this->message = $message;
    }

}