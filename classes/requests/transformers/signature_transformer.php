<?php

namespace block_quickmail\requests\transformers;

class signature_transformer extends transformer {

    public function transform_form_data()
    {
        $this->transformed_data->id = (int) $this->form_data->select_signature_id;
        $this->transformed_data->title = (string) $this->form_data->title;
        $this->transformed_data->signature = (string) $this->form_data->signature_editor['text'];
        $this->transformed_data->default_flag = $this->if_exists('default_flag');
    }

}