<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

class block_quickmail_alternate_manager_testcase extends advanced_testcase {
    
    use has_general_helpers;

    public function test_successfully_parses_body_with_no_keys()
    {
        $this->resetAfterTest(true);
 
        $this->assertTrue(true);
    }

}