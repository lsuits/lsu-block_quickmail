<?php

namespace block_quickmail\requests\transformers;

class alternate_transformer extends transformer {

    public function transform_form_data()
    {
        // $this->transformed_data->setup_user_id = (int) $this->form->user->id;
        // $this->transformed_data->firstname = (string) $this->form_data->firstname;
        // $this->transformed_data->lastname = (string) $this->form_data->lastname;
        // $this->transformed_data->email = (string) $this->form_data->email;
        // $this->transformed_data->course_id = in_array($this->form_data->availability, ['only', 'course']) ? (int) $this->course->id : 0;
        // $this->transformed_data->user_id = in_array($this->form_data->availability, ['only', 'user']) ? (int) $this->form->user->id : 0;
    }

}