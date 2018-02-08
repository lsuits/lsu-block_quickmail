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
class block_quickmail extends block_list {
    
    public $course;
    public $user;
    public $context;
    public $content;

    public function init() {
        $this->title = $this->get_title();
        $this->set_course();
        $this->set_user();
        $this->set_context();
    }

    public function get_title() {
        return block_quickmail_plugin::_s('pluginname');
    }

    public function set_course() {
        global $COURSE;

        $this->course = $COURSE;
    }

    public function set_user() {
        global $USER;

        $this->user = $USER;
    }

    public function set_context() {
        $this->context = context_course::instance($this->course->id);
    }

    /**
     * Indicates which pages types this block may be added to
     * 
     * @return array
     */
    public function applicable_formats() {
        $formats = [
            'course-view' => true, 
            'mod-scorm-view' => true
        ];

        // allow to be added at the system level ??

        return array_merge($formats, [
            'site' => false, 
            'my' => false, 
        ]);
    }
    
    /**
     * Indicates that this block has its own configuration settings
     * 
     * @return bool
     */
    public function has_config() {
        return true;
    }
    
    /**
     * Sets the content to be rendered when displaying this block
     * 
     * @return object
     */
    public function get_content() {
        // cache the content for this instance
        // @TODO: should we cache the content?
        if ( ! empty($this->content)) {
            return $this->content;
        }

        $this->content = $this->get_new_content_container();

        // begin building content...

        // if this user has the ability to send quickmail messages
        if (block_quickmail_plugin::user_has_permission('cansend', $this->context)) {
            // compose message
            $this->add_item_to_content([
                'lang_key' => 'compose',
                'icon_key' => 't/email',
                'page' => 'compose',
            ]);

            // manage drafts
            $this->add_item_to_content([
                'lang_key' => 'drafts',
                'icon_key' => 'i/open',
                'page' => 'drafts',
            ]);

            // view send history
            // $this->add_item_to_content([
            //     'lang_key' => 'history',
            //     'icon_key' => 'i/duration',
            //     'page' => 'history',
            //     // 'extra_link_params' => ['type' => 'drafts'],
            // ]);

            // manage signatures
            $this->add_item_to_content([
                'lang_key' => 'manage_signatures',
                'icon_key' => 'i/edit',
                'page' => 'signatures',
            ]);

            // manage alternate send-from emails
            if (block_quickmail_plugin::user_has_permission('allowalternate', $this->context)) {
                $this->add_item_to_content([
                    'lang_key' => 'alternate',
                    'icon_key' => 't/addcontact',
                    'page' => 'alternate',
                ]);
            }

            // manage quickmail config
            if (block_quickmail_plugin::user_has_permission('canconfig', $this->context)) {
                $this->add_item_to_content([
                    'lang_key' => 'config',
                    'icon_key' => 'i/settings',
                    'page' => 'configuration',
                ]);
            }
        }

        return $this->content;
    }

    /**
     * Builds and adds an item to the content container for the given params
     * 
     * @param  array $attributes  [lang_key,icon_key,page,extra_link_params]
     * @return void
     */
    private function add_item_to_content($attributes)
    {
        $item = $this->build_item($attributes);

        $this->content->items[] = $item;
    }

    /**
     * Builds a content item (link) for the given params
     * 
     * @param  array $attributes  [lang_key,icon_key,page,extra_link_params]
     * @return uhh...
     */
    private function build_item($attributes)
    {
        global $OUTPUT;

        $label = block_quickmail_plugin::_s($attributes['lang_key']);
        
        $icon = $OUTPUT->pix_icon($attributes['icon_key'], $label, 'moodle', $this->get_content_icon_class());

        $extra_link_params = array_key_exists('extra_link_params', $attributes) ? $attributes['extra_link_params'] : [];
        
        return html_writer::link(
            new moodle_url('/blocks/quickmail/' . $attributes['page'] . '.php', $this->get_content_link_params($extra_link_params)),
            $icon . $label
        );
    }

    /**
     * Returns an empty "block list" content container to be filled with content
     * 
     * @return object
     */
    private function get_new_content_container()
    {
        $content = new stdClass;
        
        $content->items = [];
        $content->icons = [];
        $content->footer = '';

        return $content;
    }

    private function get_content_icon_class()
    {
        return ['class' => 'icon'];
    }

    private function get_content_link_params($extra_params = [])
    {
        return ['courseid' => $this->course->id] + $extra_params;
    }
}
