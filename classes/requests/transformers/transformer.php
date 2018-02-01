<?php

namespace block_quickmail\requests\transformers;

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

    public function if_exists($prop, $default = 0)
    {
        return property_exists($this->form_data, $prop)
            ? $this->form_data->$prop
            : $default;
    }
    
    public function transform()
    {
        $this->transform_form_data();
        
        return $this->transformed_data;
    }

}