<?php

namespace block_quickmail\messenger\factories\course_recipient_send;

interface recipient_send_factory_interface {

    public function set_factory_params();
    public function set_factory_computed_params();
    public function send();
    public function send_to_mentors();

}