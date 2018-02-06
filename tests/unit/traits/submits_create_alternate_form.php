<?php

////////////////////////////////////////////////////
///
///  CREATE ALTERNATE FORM SUBMISSION HELPERS
///  
///  needs:
///   # has_general_helpers
/// 
////////////////////////////////////////////////////

trait submits_create_alternate_form {

    public function get_create_alternate_form_submission(array $override_params = [])
    {
        $params = $this->get_create_alternate_form_submission_params($override_params);

        $form_data = (object)[];

        $form_data->email = $params['email']; // default: different@email.com
        $form_data->firstname = $params['firstname']; // default: Firsty
        $form_data->lastname = $params['lastname']; // default: Lasty
        $form_data->availability = $params['availability']; // default: alternate_availability_only

        return $form_data;
    }

    public function get_create_alternate_form_submission_params(array $override_params)
    {
        $params = [];

        $params['email'] = array_key_exists('email', $override_params) ? $override_params['email'] : 'different@email.com';
        $params['firstname'] = array_key_exists('firstname', $override_params) ? $override_params['firstname'] : 'Firsty';
        $params['lastname'] = array_key_exists('lastname', $override_params) ? $override_params['lastname'] : 'Lasty';
        $params['availability'] = array_key_exists('availability', $override_params) ? $override_params['availability'] : 'alternate_availability_only';

        return $params;
    }

}