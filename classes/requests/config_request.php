<?php

namespace block_quickmail\requests;

class config_request extends \block_quickmail_request {
    
    /**
     * Helper function to report whether or not the request was submitted with intent to restore block config to course
     * 
     * @return bool
     */
    public function to_restore_defaults() {
        return $this->has_form_data_matching('restore_flag', 1);
    }

    public static function get_transformed($form_data)
    {
        return (array) $form_data;
    }

    ////////////////////////////////////////
    /// 
    ///  REDIRECTS
    /// 
    ////////////////////////////////////////
    
    //
    
}
