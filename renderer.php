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

use block_quickmail\controllers\support\controller_form_component;

use block_quickmail\components\draft_message_index_component;
use block_quickmail\components\queued_message_index_component;
use block_quickmail\components\sent_message_index_component;
use block_quickmail\components\alternate_index_component;
use block_quickmail\components\notification_index_component;
use block_quickmail\components\view_message_component;

use block_quickmail\components\broadcast_message_component;
use block_quickmail\components\broadcast_recipient_filter_results_component;
use block_quickmail\components\compose_message_component;

class block_quickmail_renderer extends plugin_renderer_base {

    ////////////////////////////////////////
    /// CONTROLLER FORM COMPONENTS
    ////////////////////////////////////////
    
    public function controller_form_component(controller_form_component $component) {
        return $this->render($component);
    }

    protected function render_controller_form_component(controller_form_component $component) {
        $out = '';
        
        // render heading, if it exists
        if (property_exists($component, 'heading')) {
            $out .= $this->output->heading(format_string($component->heading), 2);
        }

        // render any forms
        $out .= $component->form->render();

        return $this->output->container($out, 'controller_form_component');
    }

    ////////////////////////////////////////
    /// CONTROLLER TEMPLATES
    ////////////////////////////////////////

    public function controller_component_template($component_name, $params = []) {
        // get class full component class name
        $component_class = 'block_quickmail\components\\' . $component_name . '_component';

        // instantiate component including params
        $component = new $component_class($params);

        return $this->render($component);
    }

    protected function render_sent_message_index_component(sent_message_index_component $sent_message_index_component) {
        return $this->render_from_template('block_quickmail/sent_message_index', $sent_message_index_component->export_for_template($this));
    }

    protected function render_queued_message_index_component(queued_message_index_component $queued_message_index_component) {
        return $this->render_from_template('block_quickmail/queued_message_index', $queued_message_index_component->export_for_template($this));
    }

    protected function render_draft_message_index_component(draft_message_index_component $draft_message_index_component) {
        return $this->render_from_template('block_quickmail/draft_message_index', $draft_message_index_component->export_for_template($this));
    }

    protected function render_alternate_index_component(alternate_index_component $alternate_index_component) {
        return $this->render_from_template('block_quickmail/alternate_index', $alternate_index_component->export_for_template($this));
    }

    protected function render_notification_index_component(notification_index_component $notification_index_component) {
        return $this->render_from_template('block_quickmail/notification_index', $notification_index_component->export_for_template($this));
    }

    protected function render_view_message_component(view_message_component $view_message_component) {
        return $this->render_from_template('block_quickmail/view_message', $view_message_component->export_for_template($this));
    }

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

}