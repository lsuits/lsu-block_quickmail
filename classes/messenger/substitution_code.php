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

namespace block_quickmail\messenger;

class substitution_code {

    public static $codes = [
        'user' => [
            'firstname',
            'lastname',
            'firstname',
            'middlename',
            'lastname',
            'email',
            'alternatename',
        ],
        'course' => [
            'coursefullname',
            'courseshortname',
            'courseidnumber',
            'coursesummary',
            'coursestartdate',
            'courseenddate',
            'courselink',
            'seensince',
        ],
        'activity' => [
            'activityname',
            'activityduedate',
            'activitylink',
            'gradelink',
        ],
    ];

    /**
     * Returns an array of codes for the given type
     * 
     * If a string is passed, will return all codes for that type
     * If an array is passed, will return all codes for those types
     * If null is passed (default), will return all codes
     * 
     * @param  mixed  $type  string, array, defaults to null
     * @return array
     */
    public static function get($type = null)
    {
        if (is_string($type)) {
            return self::$codes[$type];
        } 

        $types = is_null($type)
            ? array_keys(self::$codes)
            : $type;

        return self::get_for_types($types);
    }

    /**
     * Returns an array of codes for the given types
     * 
     * @param  array  $types  type keys
     * @return array
     */
    private static function get_for_types($types)
    {
        $codes = [];

        foreach (array_keys(self::$codes) as $type) {
            if (in_array($type, $types)) {
                $codes = array_merge($codes, self::$codes[$type]);
            }
        }

        return $codes;
    }

}