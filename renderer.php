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
use block_quickmail\renderables\draft_message_index_component;
use block_quickmail\renderables\manage_signatures_component;
use block_quickmail\renderables\course_config_component;
use block_quickmail\renderables\alternate_index_component;
use block_quickmail\renderables\manage_alternates_component;
use block_quickmail\renderables\manage_drafts_component;

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
    /// DRAFT MESSAGE INDEX
    ////////////////////////////////////////
    
    public function draft_message_index_component($params = []) {
        $draft_message_index_component = new draft_message_index_component($params);
        
        return $this->render($draft_message_index_component);
    }

    protected function render_draft_message_index_component(draft_message_index_component $draft_message_index_component) {
        $data = $draft_message_index_component->export_for_template($this);

        return $this->render_from_template('block_quickmail/draft_message_index', $data);
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

    ////////////////////////////////////////
    /// ALTERNATE EMAILS INDEX
    ////////////////////////////////////////
    
    public function alternate_index_component($params = []) {
        $alternate_index_component = new alternate_index_component($params);
        
        return $this->render($alternate_index_component);
    }

    protected function render_alternate_index_component(alternate_index_component $alternate_index_component) {
        $data = $alternate_index_component->export_for_template($this);

        return $this->render_from_template('block_quickmail/alternate_index', $data);
    }

    ////////////////////////////////////////
    /// MANAGE ALTERNATE EMAILS FORM
    ////////////////////////////////////////
    
    public function manage_alternates_component($params = []) {
        $component = new manage_alternates_component($params);
        
        return $this->render($component);
    }

    protected function render_manage_alternates_component(manage_alternates_component $component) {
        $out = '';
        
        // render form
        $out .= $component->form->render();

        return $this->output->container($out, 'manage_alternates_component');
    }

    ////////////////////////////////////////
    /// MANAGE DRAFTS FORM
    ////////////////////////////////////////
    
    public function manage_drafts_component($params = []) {
        $component = new manage_drafts_component($params);
        
        return $this->render($component);
    }

    protected function render_manage_drafts_component(manage_drafts_component $component) {
        $out = '';
        
        // render form
        $out .= $component->form->render();

        return $this->output->container($out, 'manage_drafts_component');
    }

}