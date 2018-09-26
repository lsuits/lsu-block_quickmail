<?php

$observers = [

    //////////////////////////////
    ///
    /// COURSE VIEWED
    /// 
    //////////////////////////////
    [
        'eventname' => '\core\event\course_viewed',
        'callback'  => '\block_quickmail\notifier\event_observer::course_viewed'
    ]

];