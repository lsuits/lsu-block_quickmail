<?php

namespace block_quickmail\requests;

use block_quickmail\requests\transformers\alternate_transformer;

class alternate_request extends \block_quickmail_request {
    
    /**
     * Helper function to report whether or not the request was submitted with intent to create an alternate
     * 
     * @return bool
     */
    public function to_create_alternate() {
        return $this->has_form_data_matching('create_flag', 1);
    }

    /**
     * Helper function to report whether or not the request was submitted with intent to confirm an alternate
     * 
     * @return bool
     */
    public function to_confirm_alternate() {
        // return ! empty($this->confirm_alternate_id) && ! empty($this->confirm_alternate_token);
    }

    /**
     * Helper function to report whether or not the request was submitted with intent to resend a confirmation link
     * 
     * @return bool
     */
    public function to_resend_alternate() {
        // return (bool) $this->resend_confirm_id;
    }

    /**
     * Helper function to report whether or not the request was submitted with intent to delete an alternate
     * 
     * @return bool
     */
    public function to_delete_alternate() {
        if ( ! $this->has_form_data_key('delete_alternate_id')) {
            return false;
        }

        // return (bool) $this->delete_alternate_id;
    }

    public static function get_transformed($form_data)
    {
        $transformer = new alternate_transformer($form_data);

        return $transformer->transform();
    }

    ////////////////////////////////////////
    /// 
    ///  REDIRECTS
    /// 
    ////////////////////////////////////////
    
    //
    
}
