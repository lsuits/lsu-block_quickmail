<?php

namespace block_quickmail\transformers;

class transformer {

    public $form_data;
    public $transformed_data;

    /**
     * Construct the transformer
     * 
     * @param object  $form_data  the submitted mform data object
     */
    public function __construct($form_data) {
        $this->form_data = $form_data;;
        $this->transformed_data = (object)[];
    }

    public function transform()
    {
        $this->transform_form_data();
        
        return $this->transformed_data;
    }

    public static function get_transformed($form_data)
    {
        $transformer = new compose_transformer($form_data);

        return $transformer->transform();
    }

}