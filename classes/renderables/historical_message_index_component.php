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
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\renderables;

use block_quickmail\renderables\renderable_component;
use block_quickmail_plugin;
use moodle_url;

class historical_message_index_component extends renderable_component implements \renderable {

    public $historical_messages;

    public $user;
    
    public $course_id;
    
    public $course_historical_messages;

    public $user_course_array;

    public function __construct($params = []) {
        parent::__construct($params);
        $this->historical_messages = $this->get_param('historical_messages');
        $this->user = $this->get_param('user');
        $this->course_id = $this->get_param('course_id');
        $this->course_historical_messages = $this->filter_messages_by_course($this->historical_messages, $this->course_id);
        $this->user_course_array = $this->get_user_course_array($this->historical_messages, $this->course_id);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template($output) {
        $data = (object)[];

        $data->userCourseArray = $this->transform_course_array($this->user_course_array, $this->course_id);

        $data->courseId = $this->course_id;

        $data->tableRows = [];
        
        foreach ($this->course_historical_messages as $message) {
            $data->tableRows[] = [
                'id' => $message->get('id'),
                'courseName' => $this->user_course_array[$message->get('course_id')],
                'subjectPreview' => $message->get_subject_preview(24),
                'status' => ucfirst($message->get_status()),
                'sentScheduledDate' => $message->is_queued_message() ? $message->get_readable_to_send_at() : $message->get_readable_sent_at(),
                'openUrl' => '/blocks/quickmail/compose.php?' . http_build_query([
                    'courseid' => $message->get('course_id'),
                    'draftid' => $message->get('id')
                ], '', '&')
            ];
        }

        $data->urlBack = $this->course_id 
            ? new moodle_url('/course/view.php', ['id' => $this->course_id])
            : new moodle_url('/my');

        $data->urlBackLabel = $this->course_id 
            ? block_quickmail_plugin::_s('back_to_course')
            : 'Back to My page'; // TODO - make this a lang string

        return $data;
    }

}