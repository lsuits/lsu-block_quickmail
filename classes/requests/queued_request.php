<?php

namespace block_quickmail\requests;

use block_quickmail\requests\transformers\queued_transformer;

class queued_request extends \block_quickmail_request {
    
    /**
     * Helper function to report whether or not the request was submitted with intent to unqueue a message
     * 
     * @return bool
     */
    public function to_unqueue_message() {
        return $this->has_non_empty_form_data('unqueue_message_id');
    }

    /**
     * Helper function to report whether or not the request was submitted with intent to send a queued message immediately
     * 
     * @return bool
     */
    public function to_send_message_now() {
        return $this->has_non_empty_form_data('send_now_message_id');
    }

    public static function get_transformed($form_data)
    {
        $transformer = new queued_transformer($form_data);

        return $transformer->transform();
    }

    ////////////////////////////////////////
    /// 
    ///  REDIRECTS
    /// 
    ////////////////////////////////////////
    
    //
    
}
