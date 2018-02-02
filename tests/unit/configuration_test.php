<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

class block_quickmail_configuration_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses;

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

    public function test_updates_a_courses_config()
    {
        $this->resetAfterTest(true);

        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $default_params = block_quickmail_config::block('', false);

        $new_params = [
            'allowstudents' => '1',
            'roleselection' => '1,2',
            'receipt' => '1',
            'prepend_class' => 'fullname',
            'ferpa' => 'noferpa',
            'downloads' => '1',
            'additionalemail' => '1',
            'default_message_type' => 'email',
            'message_types_available' => 'email',
            'allowed_user_fields' => 'firstname',
        ];

        // update the courses config
        block_quickmail_config::update_course_config($course, $new_params);

        // get the courses new config
        $course_config = block_quickmail_config::course($course, '', false);

        // check attributes that CAN be changed by a course
        $this->assertNotEquals($default_params['allowstudents'], $course_config['allowstudents']);
        $this->assertNotEquals($default_params['roleselection'], $course_config['roleselection']);
        $this->assertNotEquals($default_params['receipt'], $course_config['receipt']);
        $this->assertNotEquals($default_params['prepend_class'], $course_config['prepend_class']);
        $this->assertNotEquals($default_params['default_message_type'], $course_config['default_message_type']);
        
        // check attributes that CANNOT be changed by a course (only changed at system level)
        $this->assertEquals($default_params['ferpa'], $course_config['ferpa']);
        $this->assertEquals($default_params['downloads'], $course_config['downloads']);
        $this->assertEquals($default_params['additionalemail'], $course_config['additionalemail']);
        $this->assertEquals($default_params['message_types_available'], $course_config['message_types_available']);
        $this->assertEquals($default_params['allowed_user_fields'], $course_config['allowed_user_fields']);
    }

    public function test_restores_a_courses_config_to_default()
    {
        $this->resetAfterTest(true);

        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $default_params = block_quickmail_config::block('', false);

        $new_params = [
            'allowstudents' => '1',
            'roleselection' => '1,2',
            'receipt' => '1',
            'prepend_class' => 'fullname',
            'ferpa' => 'noferpa',
            'downloads' => '1',
            'additionalemail' => '1',
            'default_message_type' => 'email',
            'message_types_available' => 'email',
            'allowed_user_fields' => 'firstname',
        ];

        // update the courses config
        block_quickmail_config::update_course_config($course, $new_params);

        // get the courses new config
        $course_config = block_quickmail_config::course($course, '', false);

        // check attributes that CAN be changed by a course
        $this->assertNotEquals($default_params['allowstudents'], $course_config['allowstudents']);
        $this->assertNotEquals($default_params['roleselection'], $course_config['roleselection']);
        $this->assertNotEquals($default_params['receipt'], $course_config['receipt']);
        $this->assertNotEquals($default_params['prepend_class'], $course_config['prepend_class']);
        $this->assertNotEquals($default_params['default_message_type'], $course_config['default_message_type']);
        
        // restore to default config
        block_quickmail_config::delete_course_config($course);

        // get the courses new (default) config
        $course_config = block_quickmail_config::course($course, '', false);

        $this->assertEquals($default_params['allowstudents'], $course_config['allowstudents']);
        $this->assertEquals($default_params['roleselection'], $course_config['roleselection']);
        $this->assertEquals($default_params['receipt'], $course_config['receipt']);
        $this->assertEquals($default_params['prepend_class'], $course_config['prepend_class']);
        $this->assertEquals($default_params['default_message_type'], $course_config['default_message_type']);
        $this->assertEquals($default_params['ferpa'], $course_config['ferpa']);
        $this->assertEquals($default_params['downloads'], $course_config['downloads']);
        $this->assertEquals($default_params['additionalemail'], $course_config['additionalemail']);
        $this->assertEquals($default_params['message_types_available'], $course_config['message_types_available']);
        $this->assertEquals($default_params['allowed_user_fields'], $course_config['allowed_user_fields']);
    }

}