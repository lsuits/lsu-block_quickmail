<?php

namespace block_quickmail\notifier\models\interfaces;

interface reminder_notification_model_interface {

    public function get_user_ids_to_notify();

}