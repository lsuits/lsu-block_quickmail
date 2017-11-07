<?php

namespace block_quickmail\messenger\factories;

interface message_factory_interface {

    public static function make($params = []);

    public function send_message($recipient_user);

}