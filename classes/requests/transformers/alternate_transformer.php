<?php

namespace block_quickmail\requests\transformers;

class alternate_transformer extends transformer {

    public function transform_form_data()
    {
        $this->transformed_data->delete_alternate_id = (int) $this->form_data->delete_alternate_id;
        $this->transformed_data->create_flag = (int) $this->form_data->create_flag;
        $this->transformed_data->firstname = (string) $this->form_data->firstname;
        $this->transformed_data->lastname = (string) $this->form_data->lastname;
        $this->transformed_data->email = (string) $this->form_data->email;
        $this->transformed_data->availability = (string) $this->form_data->availability;
    }

}