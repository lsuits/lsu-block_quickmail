<?php

namespace block_quickmail\requests;

use block_quickmail\transformers\compose_transformer;

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

    public static function get_transformed_post_data($form_data)
    {
        return compose_transformer::get_transformed($form_data);
    }


    // redirect_back_to_course_after_send
    
    // redirect_back_to_course_after_save
    
}
