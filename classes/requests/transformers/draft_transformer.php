<?php

namespace block_quickmail\requests\transformers;

class draft_transformer extends transformer {

    public function transform_form_data()
    {
        $this->transformed_data->delete_draft_id = (int) $this->form_data->delete_draft_id;
    }

}