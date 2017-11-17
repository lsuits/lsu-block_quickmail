<?php

namespace block_quickmail\drafter\exceptions;

class drafter_authentication_exception extends \Exception {
    
    public $message;

    public function __construct($message = 'Drafter authentication exception') {
        $this->message = $message;
    }

}