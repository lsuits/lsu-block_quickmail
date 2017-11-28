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

namespace block_quickmail\requests;

use block_quickmail\forms\manage_drafts_form;

class draft_request extends \block_quickmail_request {

    public $form;

    public $form_data;

    public $course_id;

    public static $public_attributes = [
        'delete_draft_id',
    ];

    /**
     * Construct the draft submission request
     * 
     * @param manage_drafts_form  $manage_drafts_form  (extends moodleform)
     */
    public function __construct(manage_drafts_form $manage_drafts_form) {
        $this->form = $manage_drafts_form;
        $this->form_data = ! empty($this->form) ? $this->form->get_data() : null;
        $this->course_id = $this->form->course_id;
    }
    
    /////////////////////////////////////////////////////////////
    ///
    ///  INSTANTIATION
    ///
    /////////////////////////////////////////////////////////////

    /**
     * Instantiates and returns a draft request
     * 
     * @param  \manage_drafts_form   $manage_drafts_form
     * @return \draft_request
     */
    public static function make(manage_drafts_form $manage_drafts_form) {
        // instantiate "draft" request
        $request = new self(
            $manage_drafts_form
        );

        return $request;
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  FORM SUBMISSION ACTIONS
    ///
    /////////////////////////////////////////////////////////////

    /**
     * Helper function to report whether or not the request was submitted with intent to delete a draft
     * 
     * @return bool
     */
    public function to_delete_draft() {
        if ( ! $this->has_form_data_key('delete_draft_id')) {
            return false;
        }

        return (bool) $this->delete_draft_id;
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  ATTRIBUTES
    ///
    /////////////////////////////////////////////////////////////
    
    /**
     * Returns an int representing the id of the draft to be deleted
     * 
     * @return int
     */
    public function delete_draft_id($form_data = null) {
        if (empty($form_data)) {
            return 0;
        }

        return ! empty($this->form_data->delete_draft_id) ? (int) $this->form_data->delete_draft_id : 0;
    }

}
