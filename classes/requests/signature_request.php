<?php

namespace block_quickmail\requests;

use block_quickmail\requests\transformers\signature_transformer;
use block_quickmail\persistents\signature;

class signature_request extends \block_quickmail_request {
    
    /**
     * Reports whether or not the request was submitted with intent to delete
     * 
     * @return bool
     */
    public function to_delete_signature() {
        return $this->has_form_data_matching('delete_signature_flag', 1);
    }

    public static function get_transformed($form_data)
    {
        $transformer = new signature_transformer($form_data);

        return $transformer->transform();
    }

    ////////////////////////////////////////
    /// 
    ///  REDIRECTS
    /// 
    ////////////////////////////////////////
    
    /**
     * Returns a redirect header towards the given user's edit default signature page
     * If a course_id is given, it will be passed in the redirect to preserve user experience
     * 
     * @param  string     $notification_type
     * @param  core_user  $user
     * @param  string     $notification_text
     * @return (http redirect header)
     */
    public function redirect_to_user_default_signature($notification_type, $user, $course_id = null, $notification_text = null) {
        // get the user's default signature id, or default to 0
        if ($signature = signature::get_default_signature_for_user($user->id)) {
            $signature_id = $signature->get('id');
        } else {
            $signature_id = 0;
        }

        // redirect to the edit signature page
        self::redirect_to_edit_signature_id($notification_type, $signature_id, $course_id, $notification_text);
    }

    /**
     * Returns a redirect header towards the given signature id's edit page
     * If a course_id is given, it will be passed in the redirect to preserve user experience
     * 
     * @param  string     $notification_type
     * @param  int        $signature_id
     * @param  string     $notification_text
     * @return (http redirect header)
     */
    public function redirect_to_edit_signature_id($notification_type, $signature_id, $course_id = null, $notification_text = null) {
        $course_id = $course_id ?: 0;

        $this->redirect_as_type($notification_type, $notification_text, '/blocks/quickmail/signature.php', ['id' => $signature_id, 'courseid' => $course_id]);
    }
    
}
