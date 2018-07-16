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

use block_quickmail\components\broadcast_message_component;
use block_quickmail\components\broadcast_recipient_filter_results_component;
use block_quickmail\components\compose_message_component;
use block_quickmail\components\draft_message_index_component;
use block_quickmail\components\queued_message_index_component;
use block_quickmail\components\sent_message_index_component;
use block_quickmail\components\manage_signatures_component;
use block_quickmail\components\course_config_component;
use block_quickmail\components\alternate_index_component;
use block_quickmail\components\manage_alternates_component;
use block_quickmail\components\manage_drafts_component;
use block_quickmail\components\manage_queued_component;
use block_quickmail\components\notification_index_component;
//
use block_quickmail\controllers\components\create_notification_component;

class block_quickmail_renderer extends plugin_renderer_base {

    ////////////////////////////////////////
    /// CONTROLLER COMPONENT - CREATE NOTIFICATION
    ////////////////////////////////////////
    
    public function controller_component($component) {
        return $this->render($component);
    }

    // TODO type hint here with controller component class
    protected function render_controller_component($component) {
        $out = '';
        
        // render heading, if it exists
        if (property_exists($component, 'heading')) {
            $out .= $this->output->heading(format_string($component->heading), 2);
        }

        // render any forms
        $out .= $component->form->render();

        return $this->output->container($out, 'controller_component');
    }

    ///////////////////////////// OLD STUFF....

    ////////////////////////////////////////
    /// BROADCAST FORM
    ////////////////////////////////////////
    
    public function broadcast_message_component($params = []) {
        $broadcast_message_component = new broadcast_message_component($params);
        
        return $this->render($broadcast_message_component);
    }

    protected function render_broadcast_message_component(broadcast_message_component $broadcast_message_component) {
        $out = '';
        
        // render heading
        $out .= $this->output->heading(format_string($broadcast_message_component->heading), 2);

        // render compose form
        $out .= $broadcast_message_component->broadcast_form->render();

        return $this->output->container($out, 'broadcast_message_component');
    }

    ////////////////////////////////////////
    /// BROADCAST RECIPIENT FILTER RESULTS
    ////////////////////////////////////////
    
    public function broadcast_recipient_filter_results_component($params = []) {
        $broadcast_recipient_filter_results_component = new broadcast_recipient_filter_results_component($params);
        
        return $this->render($broadcast_recipient_filter_results_component);
    }

    protected function render_broadcast_recipient_filter_results_component(broadcast_recipient_filter_results_component $broadcast_recipient_filter_results_component) {
        $data = $broadcast_recipient_filter_results_component->export_for_template($this);

        return $this->render_from_template('block_quickmail/broadcast_recipient_filter_results', $data);
    }

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
    /// ALTERNATE EMAILS INDEX (DISPLAY)
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
    /// DRAFT MESSAGE INDEX (DISPLAY)
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

    ////////////////////////////////////////
    /// QUEUED MESSAGE INDEX (DISPLAY)
    ////////////////////////////////////////
    
    public function queued_message_index_component($params = []) {
        $queued_message_index_component = new queued_message_index_component($params);
        
        return $this->render($queued_message_index_component);
    }

    protected function render_queued_message_index_component(queued_message_index_component $queued_message_index_component) {
        $data = $queued_message_index_component->export_for_template($this);

        return $this->render_from_template('block_quickmail/queued_message_index', $data);
    }

    ////////////////////////////////////////
    /// MANAGE QUEUED FORM
    ////////////////////////////////////////
    
    public function manage_queued_component($params = []) {
        $component = new manage_queued_component($params);
        
        return $this->render($component);
    }

    protected function render_manage_queued_component(manage_queued_component $component) {
        $out = '';
        
        // render form
        $out .= $component->form->render();

        return $this->output->container($out, 'manage_queued_component');
    }

    ////////////////////////////////////////
    /// SENT MESSAGE INDEX
    ////////////////////////////////////////
    
    public function sent_message_index_component($params = []) {
        $sent_message_index_component = new sent_message_index_component($params);
        
        return $this->render($sent_message_index_component);
    }

    protected function render_sent_message_index_component(sent_message_index_component $sent_message_index_component) {
        $data = $sent_message_index_component->export_for_template($this);

        return $this->render_from_template('block_quickmail/sent_message_index', $data);
    }

    ////////////////////////////////////////
    /// NOTIFICATION INDEX
    ////////////////////////////////////////
    
    public function notification_index_component($params = []) {
        $notification_index_component = new notification_index_component($params);
        
        return $this->render($notification_index_component);
    }

    protected function render_notification_index_component(notification_index_component $notification_index_component) {
        $data = $notification_index_component->export_for_template($this);

        return $this->render_from_template('block_quickmail/notification_index', $data);
    }

}