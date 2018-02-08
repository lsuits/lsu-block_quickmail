<?php

namespace block_quickmail\requests;

use block_quickmail\requests\transformers\draft_transformer;

class draft_request extends \block_quickmail_request {
    
    /**
     * Helper function to report whether or not the request was submitted with intent to delete a draft
     * 
     * @return bool
     */
    public function to_delete_draft() {
        return $this->has_non_empty_form_data('delete_draft_id');
    }

    /**
     * Helper function to report whether or not the request was submitted with intent to duplicate a draft
     * 
     * @return bool
     */
    public function to_duplicate_draft() {
        return $this->has_non_empty_form_data('duplicate_draft_id');
    }

    public static function get_transformed($form_data)
    {
        $transformer = new draft_transformer($form_data);

        return $transformer->transform();
    }

    ////////////////////////////////////////
    /// 
    ///  REDIRECTS
    /// 
    ////////////////////////////////////////
    
    //
    
}
