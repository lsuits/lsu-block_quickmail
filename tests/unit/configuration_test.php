<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

class block_quickmail_configuration_testcase extends advanced_testcase {
    
    use has_general_helpers;

    public function test_fetches_block_config_as_array()
    {
        $this->resetAfterTest(true);
 
        $config = block_quickmail_config::block();

        $this->assertInternalType('array', $config);
    }

    public function test_fetches_course_config_as_array()
    {
        $this->resetAfterTest(true);
 
        $course = $this->getDataGenerator()->create_course();

        $config = block_quickmail_config::course($course);

        $this->assertInternalType('array', $config);
    }

    public function test_fetches_course_id_config_as_array()
    {
        $this->resetAfterTest(true);
 
        $course = $this->getDataGenerator()->create_course();

        $config = block_quickmail_config::course($course->id);

        $this->assertInternalType('array', $config);
    }

    public function test_transforms_allowed_user_fields_into_array()
    {
        $transformed = block_quickmail_config::get_transformed($this->get_course_config_params());

        $this->assertInternalType('array', $transformed['allowed_user_fields']);
    }

    // need to write tests for:
        // update_course_config
        // delete_course_config

}