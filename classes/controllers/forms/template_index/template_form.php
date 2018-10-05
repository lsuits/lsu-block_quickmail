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

namespace block_quickmail\controllers\forms\template_index;

require_once $CFG->libdir . '/formslib.php';

use block_quickmail\controllers\support\controller_form;
use block_quickmail_string;
use block_quickmail_config;

class template_form extends controller_form {

    /*
     * Moodle form definition
     */
    public function definition() {

        $mform =& $this->_form;

        ////////////////////////////////////////////////////////////
        ///  view_form_name directive: TO BE INCLUDED ON ALL FORMS :/
        ////////////////////////////////////////////////////////////
        $mform->addElement('hidden', 'view_form_name');
        $mform->setType('view_form_name', PARAM_TEXT);
        $mform->setDefault('view_form_name', $this->get_view_form_name());

        ////////////////////////////////////////////////////////////
        ///  select_template_id (select)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'select', 
            'select_template_id', 
            block_quickmail_string::get('select_template_for_edit'), 
            $this->get_template_options()
        );
        $mform->setType(
            'select_template_id', 
            PARAM_INT
        );
        $mform->setDefault(
            'select_template_id', 
            $this->get_selected_template('id') ?: 0
        );

        $mform->addElement('html', '<hr>');

        $mform->addElement('hidden', 'template_id');
        $mform->setType('template_id', PARAM_TEXT);
        $mform->setDefault('template_id', $this->get_selected_template('id') ?: 0);

        ////////////////////////////////////////////////////////////
        ///  title (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'text', 
            'title', 
            block_quickmail_string::get('title')
        );
        $mform->setType(
            'title', 
            PARAM_TEXT
        );
        $mform->setDefault(
            'title', 
            $this->get_selected_template('title')
        );

        $mform->addRule('title', get_string('required'), 'required', '', 'server');

        ////////////////////////////////////////////////////////////
        ///  header_content textarea
        ////////////////////////////////////////////////////////////
        
        $mform->addElement(
            'textarea', 
            'header_content', 
            block_quickmail_string::get('template_header_content'), 
            'wrap="virtual" rows="20" cols="50"'
        );

        $mform->setDefault(
            'header_content', 
            $this->get_selected_template('header_content')
        );

        $mform->addRule('header_content', get_string('required'), 'required', '', 'server');

        ////////////////////////////////////////////////////////////
        ///  footer_content textarea
        ////////////////////////////////////////////////////////////
        
        $mform->addElement(
            'textarea', 
            'footer_content', 
            block_quickmail_string::get('template_footer_content'), 
            'wrap="virtual" rows="20" cols="50"'
        );

        $mform->setDefault(
            'footer_content', 
            $this->get_selected_template('footer_content')
        );

        ////////////////////////////////////////////////////////////
        ///  is_default (checkbox)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'checkbox', 
            'is_default', 
            get_string('default')
        );
        $mform->setType(
            'is_default', 
            PARAM_BOOL
        );
        $mform->setDefault(
            'is_default', 
            $this->get_selected_template('is_default')
        );

        ////////////////////////////////////////////////////////////
        ///  buttons
        ////////////////////////////////////////////////////////////
        $buttons = [
            $mform->createElement('cancel', 'cancelbutton', get_string('back')),
        ];

        if ($this->get_selected_template('id')) {
            $buttons = array_merge($buttons, [
                $mform->createElement('submit', 'update', get_string('update')),
                $mform->createElement('submit', 'delete', get_string('delete')),
            ]);
        } else {
            $buttons = array_merge($buttons, [
                $mform->createElement('submit', 'save', get_string('save', 'block_quickmail')),
            ]);
        }
        
        $mform->addGroup($buttons, 'actions', '&nbsp;', array(' '), false);
    }

    /**
     * Returns the current templates for selection with a prepended "new template" option
     * 
     * @return array
     */
    private function get_template_options()
    {
        return [0 => get_string('createnew', 'moodle')] + $this->get_custom_data('template_array');
    }

    /**
     * Returns the given param for the currently selected template, if any, defaulting to empty string
     * 
     * @param  mixed  $attr
     * @return mixed
     */
    private function get_selected_template($attr)
    {
        return ! empty($this->get_custom_data('selected_template'))
            ? $this->get_custom_data('selected_template')->get($attr)
            : '';
    }

}
