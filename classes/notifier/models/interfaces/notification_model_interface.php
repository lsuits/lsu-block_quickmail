<?php

namespace block_quickmail\notifier\models\interfaces;

interface notification_model_interface {

    // public $component;
    // public $object;
    // public $required_conditions;

    public function get_substitution_codes();

}