<?php

namespace block_quickmail\controllers\support;

class controller_form_component implements \renderable {

    public $form;
    public $props;
    public $heading = '';

    public function __construct($form, $params = []) {
        $this->form = $form;
        $this->props = (object) [];

        foreach ($params as $key => $value) {
            // if this is a heading, set the heading
            if ($key == 'heading') {
                $this->heading = $value;

            // otherwise, set as a prop
            } else {
                $this->props->$key = $value;
            }
        }
    }

}