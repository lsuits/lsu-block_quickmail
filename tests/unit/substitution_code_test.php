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
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\messenger\substitution_code;

class block_quickmail_substitution_code_testcase extends advanced_testcase {
    
    use has_general_helpers;

    public function test_gets_user_substitution_codes()
    {
        $codes = substitution_code::get('user');

        $this->assertCount(7, $codes);
        $this->assertContains('firstname', $codes);
        $this->assertContains('lastname', $codes);
        $this->assertContains('firstname', $codes);
        $this->assertContains('middlename', $codes);
        $this->assertContains('lastname', $codes);
        $this->assertContains('email', $codes);
        $this->assertContains('alternatename', $codes);
    }

    public function test_gets_course_substitution_codes()
    {
        $codes = substitution_code::get('course');

        $this->assertCount(8, $codes);
        $this->assertContains('coursefullname', $codes);
        $this->assertContains('courseshortname', $codes);
        $this->assertContains('courseidnumber', $codes);
        $this->assertContains('coursesummary', $codes);
        $this->assertContains('coursestartdate', $codes);
        $this->assertContains('courseenddate', $codes);
        $this->assertContains('courselink', $codes);
        $this->assertContains('seensince', $codes);
    }

    public function test_gets_activity_substitution_codes()
    {
        $codes = substitution_code::get('activity');

        $this->assertCount(4, $codes);
        $this->assertContains('activityname', $codes);
        $this->assertContains('activityduedate', $codes);
        $this->assertContains('activitylink', $codes);
        $this->assertContains('gradelink', $codes);
    }

    public function test_gets_all_codes()
    {
        $codes = substitution_code::get();

        $this->assertCount(19, $codes);
    }

    ///////////////////////////////////////////////
    ///
    /// HELPERS
    /// 
    //////////////////////////////////////////////
    
    // 

}