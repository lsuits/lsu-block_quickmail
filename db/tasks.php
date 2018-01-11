<?php

$tasks = [

    [
        'classname' => 'block_quickmail\tasks\send_unsent_course_messages_task',

        'blocking' => 0,

        'month' => '*',
        
        'day' => '*',
        
        'dayofweek' => '*',
        
        'hour' => '*',

        'minute' => '*/5', // change to */5 for production
    ]

];