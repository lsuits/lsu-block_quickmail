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

namespace block_quickmail\components;

use block_quickmail\components\component;
use block_quickmail_string;
use moodle_url;
use block_quickmail\persistents\message;

class queued_message_index_component extends component implements \renderable {

    public $queued_messages;
    public $pagination;
    public $user;
    public $course_id;
    public $course_queued_messages;
    public $user_course_array;

    public function __construct($params = []) {
        parent::__construct($params);
        $this->queued_messages = $this->get_param('queued_messages');
        $this->pagination = $this->get_param('queued_pagination');
        $this->user = $this->get_param('user');
        $this->course_id = $this->get_param('course_id');
        $this->sort_by = $this->get_param('sort_by');
        $this->sort_dir = $this->get_param('sort_dir');
        $this->course_queued_messages = message::filter_messages_by_course($this->queued_messages, $this->course_id);
        $this->user_course_array = message::get_user_course_array($this->queued_messages, $this->course_id);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template($output) {
        $data = (object)[];

        // get a flat array of course id => course name
        $data->userCourseArray = $this->transform_course_array($this->user_course_array, $this->course_id);
        $data->courseId = $this->course_id;
        $data->sortBy = $this->sort_by;
        $data->isSortedAsc = $this->sort_dir == 'asc';
        $data->courseIsSorted = $this->is_attr_sorted('course');
        $data->subjectIsSorted = $this->is_attr_sorted('subject');
        $data->createdIsSorted = $this->is_attr_sorted('created');
        $data->scheduledIsSorted = $this->is_attr_sorted('scheduled');
        
        $data = $this->include_pagination($data, $this->pagination);

        $data->tableRows = [];
        
        foreach ($this->course_queued_messages as $message) {
            $data->tableRows[] = [
                'id' => $message->get('id'),
                'courseName' => $this->user_course_array[$message->get('course_id')],
                'subjectPreview' => $message->get_subject_preview(24),
                'messagePreview' => $message->get_body_preview(),
                'createdAt' => $message->get('timecreated'),
                'scheduledSendAt' => $message->get('to_send_at'),
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
            ? block_quickmail_string::get('back_to_course')
            : 'Back to My page';

        return $data;
    }

}