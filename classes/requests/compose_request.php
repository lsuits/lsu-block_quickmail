<?php

namespace block_quickmail\requests;

use block_quickmail\requests\transformers\compose_transformer;

class compose_request extends \block_quickmail_request {
    
    /**
     * Reports whether or not this request was submitted with intent to send
     * 
     * @return bool
     */
    public function to_send_message() {
        return $this->was_submitted('send');
    }

    /**
     * Reports whether or not this request was submitted with intent to save
     * 
     * @return bool
     */
    public function to_save_draft() {
        return $this->was_submitted('save');
    }

    public static function get_transformed($form_data)
    {
        $transformer = new compose_transformer($form_data);

        return $transformer->transform();
    }
    
}
