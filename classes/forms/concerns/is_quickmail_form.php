<?php

namespace block_quickmail\forms\concerns;

trait is_quickmail_form {

    public static function generate_target_url($params = [])
    {
        $target = '?' . http_build_query($params, '', '&');

        return $target;
    }

}