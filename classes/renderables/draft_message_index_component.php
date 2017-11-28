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

class draft_message_index_component extends renderable_component implements \renderable {

    public $draft_messages;

    public $user;
    
    public $course_id;
    
    public $course_draft_messages;

    public $user_course_array;

    public function __construct($params = []) {
        parent::__construct($params);
        $this->draft_messages = $this->get_param('draft_messages');
        $this->user = $this->get_param('user');
        $this->course_id = $this->get_param('course_id');
        $this->course_draft_messages = $this->filter_drafts_for_course_selection($this->draft_messages, $this->course_id);
        $this->user_course_array = $this->get_user_course_array($this->draft_messages, $this->course_id);
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
        
        foreach ($this->course_draft_messages as $message) {
            $data->tableRows[] = [
                'id' => $message->get('id'),
                'courseName' => $this->user_course_array[$message->get('course_id')],
                'subjectPreview' => $message->get_subject_preview(24),
                'messagePreview' => $message->get_body_preview(),
                'createdAt' => $message->get_readable_created_at(),
                'lastModifiedAt' => $message->get_readable_last_modified_at(),
                'openUrl' => '/blocks/quickmail/compose.php?' . http_build_query([
                    'courseid' => $message->get('course_id'),
                    'draftid' => $message->get('id')
                ], '', '&'),
                'duplicateUrl' => '/blocks/quickmail/drafts.php?' . http_build_query([
                    'courseid' => $this->course_id,
                    'duplicateid' => $message->get('id')
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

    /**
     * Returns an array of draft messages for a specific course given an array and course id
     * 
     * @param  array  $all_draft_messages
     * @param  int    $course_id
     * @return array
     */
    private function filter_drafts_for_course_selection($all_draft_messages, $course_id) {
        if ($course_id) {
            // if a course is selected, filter out any non-selected-course drafts
            $course_draft_messages = array_filter($all_draft_messages, function($draft) use ($course_id) {
                return $draft->get('course_id') == $course_id;
            });
        } else {
            // otherwise, include all draft messages
            $course_draft_messages = $all_draft_messages;
        }

        return $course_draft_messages;
    }

    /**
     * Returns an array given an array of draft messages
     * This will include the currently selected course, even if that course does not have any pending drafts
     * 
     * @param  array  $all_draft_messages
     * @param  int    $selected_course_id
     * @return array  [course id => course short name]
     */
    private function get_user_course_array($all_draft_messages, $selected_course_id = 0) {
        global $DB;
        
        // first, get all course ids from the given drafts
        $course_ids = array_reduce($all_draft_messages, function($carry, $draft) {
            $carry[] = (int) $draft->get('course_id');

            return $carry;
        }, []);

        // if a selected course id was given, be sure to include this course in the results
        if ($selected_course_id) {
            $course_ids[] = $selected_course_id;
        }

        // make sure we have unique values
        $course_ids = array_unique($course_ids, SORT_NUMERIC);

        // get course data for the given list of course ids
        $course_data = $DB->get_records_sql('SELECT id, shortname FROM {course} WHERE id in (' . implode(',', $course_ids) . ')');

        $results = [];

        // add an entry for each course to the results array
        foreach ($course_data as $course) {
            $results[(int) $course->id] = $course->shortname;
        }

        return $results;
    }

    /**
     * Returns a transformed array for template given a flat array of course id => course name
     * 
     * @param  array  $course_array
     * @param  int    $selected_course_id
     * @return array
     */
    private function transform_course_array($course_array, $selected_course_id = 0) {
        $results = [];

        foreach ($course_array as $id => $shortname) {
            $results[] = [
                'userCourseId' => (string) $id, 
                'userCourseName' => $shortname,
                'selectedAttr' => $selected_course_id == $id ? 'selected' : ''
            ];
        }

        return $results;
    }

}