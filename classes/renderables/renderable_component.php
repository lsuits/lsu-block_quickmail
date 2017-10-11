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

class renderable_component {

    protected $params;

    public function __construct($params = []) {
        $this->params = $params;
    }

    /**
     * Returns the given key from within the set params array, if any
     * Accepts optional second parameter, default value
     * 
     * @param  string $key
     * @param  mixed $default_value  an optional value to use if no results found
     * @return mixed
     */
    public function get_param($key, $default_value = null)
    {
        return isset($this->params[$key]) ? $this->params[$key] : $default_value;
    }
}