<?php

namespace block_quickmail\drafter\exceptions;

class drafter_critical_exception extends \Exception {
    
    public $message;

    public function __construct($message = 'Drafter critical exception') {
        $this->message = $message;
    }

}