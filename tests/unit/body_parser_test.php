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
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\messenger\body_parser;

class block_quickmail_body_parser_testcase extends advanced_testcase {
    
    use has_general_helpers;

    public function test_successfully_parses_body_with_no_keys()
    {
        $this->resetAfterTest(true);
 
        $body = 'Hello world!';

        $parser = new body_parser($body);

        $this->assertCount(0, $parser->message_keys);
    }

    public function test_successfully_parses_body_with_keys()
    {
        $this->resetAfterTest(true);
 
        $body = 'Hello world! Here is [:something:] and an additional [:something_else:]! I hope you enjoyed these keys.';

        $parser = new body_parser($body);

        $this->assertCount(2, $parser->message_keys);
        $this->assertContains('something', $parser->message_keys);
        $this->assertContains('something_else', $parser->message_keys);
    }

    public function test_generates_error_if_invalid_key_delimiting()
    {
        $this->resetAfterTest(true);
        
        $body = 'Hello world! This one [:here:] is just not [:right would you believe that?';

        $parser = new body_parser($body);

        $this->assertTrue($parser->has_errors());
        $this->assertEquals($parser->errors[0], 'Custom data delimiters not formatted properly.');
    }

    public function test_generates_error_if_unsupported_key_exists()
    {
        $this->resetAfterTest(true);
        
        $this->update_system_config_value('block_quickmail_allowed_user_fields', 'this,that');

        $body = 'Hello world! Here is [:this:], [:that:], and the [:other:]!';

        $parser = new body_parser($body);

        $this->assertTrue($parser->has_errors());
        $this->assertEquals($parser->errors[0], 'Custom data key "other" is not allowed.');
    }

}