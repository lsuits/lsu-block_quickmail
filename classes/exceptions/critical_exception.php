<?php

namespace block_quickmail\exceptions;

class critical_exception extends \Exception {
    
    public $message;

    public function __construct($message) {
        $this->message = $message;
    }

}