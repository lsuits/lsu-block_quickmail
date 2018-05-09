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
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\requests;

use block_quickmail\requests\transformers\draft_transformer;

class draft_request extends \block_quickmail_request {
    
    /**
     * Helper function to report whether or not the request was submitted with intent to delete a draft
     * 
     * @return bool
     */
    public function to_delete_draft() {
        return $this->has_non_empty_form_data('delete_draft_id');
    }

    /**
     * Helper function to report whether or not the request was submitted with intent to duplicate a draft
     * 
     * @return bool
     */
    public function to_duplicate_draft() {
        return $this->has_non_empty_form_data('duplicate_draft_id');
    }

    public static function get_transformed($form_data)
    {
        $transformer = new draft_transformer($form_data);

        return $transformer->transform();
    }

    ////////////////////////////////////////
    /// 
    ///  REDIRECTS
    /// 
    ////////////////////////////////////////
    
    //
    
}
