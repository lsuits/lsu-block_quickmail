<?php

namespace block_quickmail\messenger\exceptions;

class messenger_critical_exception extends \Exception {
    
    public $message;

    public function __construct($message = 'Messenger critical exception') {
        $this->message = $message;
    }

}