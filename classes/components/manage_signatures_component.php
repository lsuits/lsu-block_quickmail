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

namespace block_quickmail\components;

use block_quickmail\components\component;
use block_quickmail\persistents\signature;

class manage_signatures_component extends component implements \renderable {

    public $form;

    public $heading;
    
    public function __construct($params = []) {
        parent::__construct($params);

        // get prepared form data, including appropriate handling of signature_editor
        $prepared_signature_data = $this->get_prepared_editor_signature_data($this->get_param('signature'));

        // set the form
        $this->form = $this->get_param('manage_signatures_form');
        
        // set the form's default, prepared data
        $this->form->set_data($prepared_signature_data);

        $this->heading = false;
    }

    /**
     * Returns prepared form data, including appropriate handling of signature_editor
     * 
     * @param  signature|null  $signature     signature persistent, or null
     * @return array
     */
    private function get_prepared_editor_signature_data($signature = null)
    {
        // if no signature was passed, create a temporary record belonging to this user
        $persistent = ! empty($signature) ? $signature : new signature(0, (object) ['user_id' => $this->get_param('user')->id]);

        // convert the signature to a simple object
        $signature_record = $persistent->to_record();

        // set this user's text editor preference
        $signature_record->signatureformat = (int) $this->get_param('user')->mailformat;

        // prepare the form data to include appropriate editor content
        $prepared_signature_data = file_prepare_standard_editor(
            $signature_record, 
            'signature', 
            \block_quickmail_config::get_editor_options($this->get_param('context')), 
            $this->get_param('context'), 
            \block_quickmail_plugin::$name, 
            'signature',
            $signature_record->id
        );

        return $prepared_signature_data;
    }

}