<?php

namespace block_quickmail\requests;

use block_quickmail\requests\transformers\signature_transformer;

class signature_request extends \block_quickmail_request {
    
    /**
     * Reports whether or not the request was submitted with intent to save
     * 
     * @return bool
     */
    public function to_save_signature() {
        return $this->was_submitted('save');
    }

    /**
     * Reports whether or not the request was submitted with intent to delete
     * 
     * @return bool
     */
    public function to_delete_signature() {
        return $this->has_form_data_matching('delete_signature_flag', 1);
    }

    public static function get_transformed_post_data($form_data)
    {
        return signature_transformer::get_transformed($form_data);
    }
    
}
