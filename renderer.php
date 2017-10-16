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

require_once 'classes/forms/compose_message_form.php';
require_once 'classes/forms/manage_signatures_form.php';
require_once 'classes/forms/course_config_form.php';

use block_quickmail\renderables\compose_message_component;
use block_quickmail\renderables\manage_signatures_component;
use block_quickmail\renderables\course_config_component;

class block_quickmail_renderer extends plugin_renderer_base {

    ////////////////////////////////////////
    /// COMPOSE FORM
    ////////////////////////////////////////
    
    public function compose_message_component($params = []) {
        $compose_message_component = new compose_message_component($params);
        
        return $this->render($compose_message_component);
    }

    protected function render_compose_message_component(compose_message_component $compose_message_component) {
        $out = '';
        
        // render heading
        $out .= $this->output->heading(format_string($compose_message_component->heading), 2);

        // render compose form
        $out .= $compose_message_component->compose_form->render();

        return $this->output->container($out, 'compose_message_component');
    }

    ////////////////////////////////////////
    /// MANAGE SIGNATURES (USER) FORM
    ////////////////////////////////////////
    
    public function manage_signatures_component($params = []) {
        $component = new manage_signatures_component($params);
        
        return $this->render($component);
    }

    protected function render_manage_signatures_component(manage_signatures_component $component) {
        $out = '';
        
        // render heading
        $out .= $this->output->heading(format_string($component->heading), 2);

        // render form
        $out .= $component->form->render();

        return $this->output->container($out, 'manage_signatures_component');
    }

    ////////////////////////////////////////
    /// CONFIGURATION (COURSE) FORM
    ////////////////////////////////////////
    
    public function course_config_component($params = []) {
        $course_config_component = new course_config_component($params);
        
        return $this->render($course_config_component);
    }

    protected function render_course_config_component(course_config_component $course_config_component) {
        $out = '';
        
        // render config form
        $out .= $course_config_component->course_config_form->render();

        return $this->output->container($out, 'course_config_component');
    }

}

// $links = array();
// $gen_url = function($type) use ($COURSE) {
//     $email_param = array('courseid' => $COURSE->id, 'type' => $type);
//     return new moodle_url('emaillog.php', $email_param);
// };

// $draft_link = html_writer::link ($gen_url('drafts'), quickmail::_s('drafts'));
// $links[] =& $mform->createElement('static', 'draft_link', '', $draft_link);

// $context = context_course::instance($COURSE->id);

// $config = quickmail::load_config($COURSE->id);

// $can_send = (
//     has_capability('block/quickmail:cansend', $context) or
//     !empty($config['allowstudents'])
// );

// if ($can_send) {
//     $history_link = html_writer::link($gen_url('log'), quickmail::_s('history'));
//     $links[] =& $mform->createElement('static', 'history_link', '', $history_link);
// }