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

    public function init() {
        $this->title = $this->get_title();
        // $this->version = $this->get_version();
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
        // if no course, or is "site" course, return system context
        if (empty($this->course) || $this->course->id == 1) {
            $context = block_quickmail_plugin::resolve_context('system');

        // otherwise, get the course's context
        } else {
            list($context, $course) = block_quickmail_plugin::resolve_context('course', $this->course->id);
        }

        $this->context = $context;
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

        if (block_quickmail_plugin::user_has_permission_in_context('myaddinstance', $this->context)) {
            return array_merge($formats, [
                'site' => true, 
                'my' => true, 
            ]);
        }

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
        // @TODO: find a better way to deal with unauthenticated users
        if ($this->user->id == 0) {
            return null;
        }

        // cache the content for this instance
        // @TODO: should we cache the content?
        if ( ! empty($this->content)) {
            return $this->content;
        }

        $this->content = $this->get_new_content_container();

        // begin building content...

        // if this user has the ability to send quickmail messages
        if (block_quickmail_plugin::user_has_permission_in_context('cansend', $this->context)) {
            $content_list_items = [
                // compose message
                [
                    'lang_key' => 'compose',
                    'icon_key' => 't/email',
                    'page' => 'compose',
                ],
                // manage drafts
                [
                    'lang_key' => 'drafts',
                    'icon_key' => 'i/open',
                    'page' => 'drafts',
                ],
                // manage signatures
                [
                    'lang_key' => 'manage_signatures',
                    'icon_key' => 'i/edit',
                    'page' => 'signature',
                ],
                // view send history
                // [
                //     'lang_key' => 'history',
                //     'icon_key' => 'i/settings',
                //     'page' => 'emaillog',
                //     'extra_link_params' => ['type' => 'drafts'],
                // ],
            ];

            // TODO: add in items only available in course context
            
            // manage alternate send-from emails
            if (block_quickmail_plugin::user_has_permission_in_context('allowalternate', $this->context)) {
                $content_list_items[] = [
                        'lang_key' => 'alternate',
                        'icon_key' => 'i/edit',
                        'page' => 'alternate',
                    ];
            }

            // manage quickmail config
            if (block_quickmail_plugin::user_has_permission_in_context('canconfig', $this->context)) {
                $content_list_items[] = [
                    'lang_key' => 'config',
                    'icon_key' => 'i/settings',
                    'page' => 'configuration',
                ];
            }
        }

        // construct and add all of the items to the content to be output
        foreach ($content_list_items as $item_attributes) {
            $this->add_item_to_content($item_attributes);
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
