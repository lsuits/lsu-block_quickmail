<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\messenger;

use block_quickmail\persistents\message;

interface messenger_interface {

    /////////////////////////////////////////////////////////////
    ///
    ///  MESSAGE COMPOSITION METHODS
    /// 
    /////////////////////////////////////////////////////////////

    /**
     * Creates a "compose" (course-scoped) message from the given user within the given course using the given form data
     * 
     * Depending on the given form data, this message may be sent now or at some point in the future.
     * By default, the message delivery will be handled as individual adhoc tasks which are
     * picked up by a scheduled task.
     *
     * Optionally, a draft message may be passed which will use and update the draft information
     *
     * @param  object   $user            moodle user sending the message
     * @param  object   $course          course in which this message is being sent
     * @param  array    $form_data       message parameters which will be validated
     * @param  message  $draft_message   a draft message (optional, defaults to null)
     * @param  bool     $send_as_tasks   if false, the message will be sent immediately
     * @return message
     * @throws validation_exception
     * @throws critical_exception
     */
    public static function compose($user, $course, $form_data, $draft_message = null, $send_as_tasks = true);

    /**
     * Creates an "broadcast" (admin, site-scoped) message from the given user using the given user filter and form data
     * 
     * Depending on the given form data, this message may be sent now or at some point in the future.
     * By default, the message delivery will be handled as individual adhoc tasks which are
     * picked up by a scheduled task.
     *
     * Optionally, a draft message may be passed which will use and update the draft information
     *
     * @param  object                                       $user                         moodle user sending the message
     * @param  object                                       $course                       the moodle "SITEID" course
     * @param  array                                        $form_data                    message parameters which will be validated
     * @param  block_quickmail_broadcast_recipient_filter   $broadcast_recipient_filter
     * @param  message                                      $draft_message                a draft message (optional, defaults to null)
     * @param  bool                                         $send_as_tasks                if false, the message will be sent immediately
     * @return message
     * @throws validation_exception
     * @throws critical_exception
     */
    public static function broadcast($user, $course, $form_data, $broadcast_recipient_filter, $draft_message = null, $send_as_tasks = true);

    /////////////////////////////////////////////////////////////
    ///
    ///  MESSAGE DRAFTING METHODS
    /// 
    /////////////////////////////////////////////////////////////

    /**
     * Creates a draft "compose" (course-scoped) message from the given user within the given course using the given form data
     * 
     * Optionally, a draft message may be passed which will be updated rather than created anew
     *
     * @param  object   $user            moodle user sending the message
     * @param  object   $course          course in which this message is being sent
     * @param  array    $form_data       message parameters which will be validated
     * @param  message  $draft_message   a draft message (optional, defaults to null)
     * @return message
     * @throws validation_exception
     * @throws critical_exception
     */
    public static function save_compose_draft($user, $course, $form_data, $draft_message = null);
    
    /**
     * Creates a draft "broadcast" (system-scoped) message from the given user within the given course using the given form data
     * 
     * Optionally, a draft message may be passed which will be updated rather than created anew
     *
     * @param  object                                       $user            moodle user sending the message
     * @param  object                                       $course          course in which this message is being sent
     * @param  array                                        $form_data       message parameters which will be validated
     * @param  block_quickmail_broadcast_recipient_filter   $broadcast_recipient_filter
     * @param  message                                      $draft_message   a draft message (optional, defaults to null)
     * @return message
     * @throws validation_exception
     * @throws critical_exception
     */
    public static function save_broadcast_draft($user, $course, $form_data, $broadcast_recipient_filter, $draft_message = null);

    /**
     * Creates and returns a new message given a draft message id
     * 
     * @param  int    $draft_id
     * @param  object $user       the user duplicating the draft
     * @return message
     */
    public static function duplicate_draft($draft_id, $user);

    /////////////////////////////////////////////////////////////
    ///
    ///  MESSENGER INSTANCE METHODS
    /// 
    /////////////////////////////////////////////////////////////

    /**
     * Sends the message to all of its recipients
     * 
     * @return void
     */
    public function send();

    /**
     * Sends the message to the given recipient
     * 
     * @param  message_recipient  $recipient   message recipient to recieve the message
     * @return bool
     */
    public function send_to_recipient($recipient);

    /**
     * Performs post-send actions
     * 
     * @return void
     */
    public function handle_message_post_send();

}