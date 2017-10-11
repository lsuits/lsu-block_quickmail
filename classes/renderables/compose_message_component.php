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

class compose_message_component extends renderable_component implements \renderable {

    public $compose_form;
    
    public $heading;

    public function __construct($params = []) {
        parent::__construct($params);

        $this->compose_form = $this->get_param('compose_form');
        $this->heading = $this->get_form_heading();
    }

    private function get_form_heading() {
        return \block_quickmail_plugin::_s('compose_heading', (object) [
            // @TODO: make this happen... (dynamic heading content based on context Admin/Course)
            // 'scope' => ucfirst(\block_quickmail_plugin::resolve_context_scope()), 
            'scope' => '',
            'output_channel' => ucfirst(\block_quickmail_plugin::get_output_channel())
        ]);
    }

}