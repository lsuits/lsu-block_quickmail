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

namespace block_quickmail\persistents;

// use \core\persistent;
use lang_string;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\can_be_soft_deleted;

// if ( ! class_exists('\core\persistent')) {
//     class_alias('\block_quickmail\persistents\persistent', '\core\persistent');
// }
 
class template extends \block_quickmail\persistents\persistent {
 
    use enhanced_persistent,
        can_be_soft_deleted;

    /** Table name for the persistent. */
    const TABLE = 'block_quickmail_templates';
 
    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'title' => [
                'type' => PARAM_TEXT,
            ],
            'header_content' => [
                'type' => PARAM_RAW,
            ],
            'footer_content' => [
                'type' => PARAM_RAW,
            ],
            'is_default' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'timedeleted' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }
 
    protected function validate_title($value) {
        // if this is a new template attempting to be created, check to make sure this title is unique
        if ( ! $this->get('id') && self::count_records([
            'title' => $value,
            'timedeleted' => 0
        ])) {
            return new lang_string('template_title_must_be_unique', 'block_quickmail');
        }

        if (empty($value)) {
            return new lang_string('template_title_required', 'block_quickmail');
        }

        return true;
    }

    protected function validate_header_content($value) {
        if (empty($value)) {
            return new lang_string('template_header_content_required', 'block_quickmail');
        }

        return true;
    }

    ///////////////////////////////////////////////
    ///
    ///  HOOKS
    /// 
    ///////////////////////////////////////////////

    /**
     * Take appropriate actions before creating a new template, including:
     *   
     *   - if new template is not default, and no templates exist, make it the default
     * 
     * @return void
     */
    protected function before_create() {
        $existing_default = self::get_default_template();

        if ( ! $this->is_default() && empty($existing_default)) {
            $this->set('is_default', 1);
        }
    }

    /**
     * Take appropriate actions after creating a new template, including:
     *   
     *   - if new template is default, and an existing default template exists, make the existing default NOT default
     * 
     * @return void
     */
    protected function after_create() {
        $existing_default = self::get_default_template();

        if ($this->is_default() && ! empty($existing_default)) {
            $existing_default->set('is_default', 0);
            $existing_default->update();
        }
    }

    /**
     * Take appropriate actions after updating a template, including:
     *   
     *   - if this updated template is now default, flag all others (if any), as non-default
     *   - if this updated template is NOT default, make sure there is at least one default
     * 
     * @param bool  $result  whether or not the update was successful
     * @return void
     */
    protected function after_update($result) {
        if ($result) {
            if ($this->is_default()) {
                global $DB;

                $sql = 'UPDATE {block_quickmail_templates} 
                        SET is_default = 0
                        WHERE id <> ?';

                $DB->execute($sql, [
                    $this->get('id'),
                ]);
            } else {
                $existing_default = self::get_default_template();

                if (empty($existing_default)) {
                    $this->set('is_default', 1);
                    $this->update();
                }
            }
        }
    }

    /**
     * Take appropriate actions before deleting a template, including:
     *   
     *   - if default template is deleted, set a new one if possible
     * 
     * @return void
     */
    protected function before_delete() {
        // if this template being deleted is the default template
        if ($this->is_default()) {
            // mark this deleted template as being NOT default
            $this->set('is_default', 0);

            // get all templates, if any
            $templates = self::get_records(['timedeleted' => 0]);

            // if any templates, set another as default
            foreach ($templates as $template) {
                // if this is the template being deleted, continue to next, if any
                if ($template->is_default()) {
                    continue;
                }

                // save this template as default
                $template->set('is_default', 1);
                $template->update();
            }
        }
    }

    ///////////////////////////////////////////////
    ///
    ///  FORMATTING METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Returns an html string for the given body content, formatted according to this template's
     * design
     * 
     * @param  string  $body
     * @return string
     */
    public function get_formatted($body = '')
    {
        // prepend the header, if any
        if ($header = $this->get('header_content')) {
            $body = $header . '<br>' . $body;
        }

        // append the footer, if any
        if ($footer = $this->get('footer_content')) {
            $body = $body . '<br>' . $footer;
        }

        return $body;
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Reports whether or not this template is the default
     * 
     * @return bool
     */
    public function is_default()
    {
        return (bool) $this->get('is_default');
    }

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Fetches a template by id
     * 
     * @param  integer $template_id
     * @return template|null
     */
    public static function find_template_or_null($template_id = 0)
    {
        // try to find the template by id, returning null by default
        if ( ! $template = self::find_or_null($template_id)) {
            return null;
        }

        return $template;
    }

    /**
     * Returns an array of templates
     * 
     * @return array   (template id => template title)
     */
    public static function get_flat_array()
    {
        // get all templates, if any
        $templates = self::get_records(['timedeleted' => 0]);

        $result = array_reduce($templates, function ($carry, $template) {
            $value = $template->get('title');

            if ($template->get('is_default')) {
                $value .= ' (' . get_string('default', 'moodle') . ')';
            }

            $carry[$template->get('id')] = $value;
            
            return $carry;
        }, []);

       return $result;
    }

    /**
     * Returns the default template, or null if none found
     * 
     * @return mixed      $template|null
     */
    public static function get_default_template()
    {
        if ( ! $default_template = self::get_record(['is_default' => 1, 'timedeleted' => 0])) {
            return null;
        }

        return $default_template;
    }

}