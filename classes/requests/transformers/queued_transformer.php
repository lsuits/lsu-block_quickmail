<?php

namespace block_quickmail\requests\transformers;

class queued_transformer extends transformer {

    public function transform_form_data()
    {
        $this->transformed_data->unqueue_message_id = (int) $this->form_data->unqueue_message_id;
    }

}