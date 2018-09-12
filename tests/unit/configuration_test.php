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

    public function test_fetches_role_selection_setting_as_array()
    {
        $this->resetAfterTest(true);
 
        // get default block setting (3,4,5)
        $setting = block_quickmail_config::get_role_selection_array();

        $this->assertInternalType('array', $setting);
        $this->assertCount(3, $setting);
        $this->assertContains(3, $setting);
        $this->assertContains(4, $setting);
        $this->assertContains(5, $setting);

        // get default course setting (3,4,5)
        $course = $this->getDataGenerator()->create_course();

        $setting = block_quickmail_config::get_role_selection_array($course);

        $this->assertInternalType('array', $setting);
        $this->assertCount(3, $setting);
        $this->assertContains(3, $setting);
        $this->assertContains(4, $setting);
        $this->assertContains(5, $setting);

        // update the course's settings
        $new_params = [
            'allowstudents' => '1',
            'roleselection' => '1,2',
            'receipt' => '1',
            'prepend_class' => 'fullname',
            'ferpa' => 'noferpa',
            'downloads' => '1',
            'allow_mentor_copy' => '1',
            'additionalemail' => '1',
            'default_message_type' => 'email',
            'message_types_available' => 'email',
            'send_now_threshold' => '32',
        ];

        // update the courses config
        block_quickmail_config::update_course_config($course, $new_params);

        $setting = block_quickmail_config::get_role_selection_array($course);

        $this->assertInternalType('array', $setting);
        $this->assertCount(2, $setting);
        $this->assertContains(1, $setting);
        $this->assertContains(2, $setting);
    }

    public function test_reports_course_ferpa_strictness()
    {
        $this->resetAfterTest(true);

        // create course with default setting (strictferpa)
        $course = $this->getDataGenerator()->create_course();

        $be_strict = block_quickmail_config::be_ferpa_strict_for_course($course);

        $this->assertTrue($be_strict);

        // need to test with changing system config...

        // $this->update_system_config_value('block_quickmail_ferpa', 'noferpa');
        // $this->assertFalse($be_strict);
        // $be_strict = block_quickmail_config::be_ferpa_strict_for_course($course);
        // $course = $this->getDataGenerator()->create_course(['groupmode' => 0]);
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
            'allow_mentor_copy' => '1',
            'prepend_class' => 'fullname',
            'ferpa' => 'noferpa',
            'downloads' => '1',
            'additionalemail' => '1',
            'default_message_type' => 'message',
            'message_types_available' => 'all',
            'send_now_threshold' => '32',
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
        // $this->assertNotEquals($default_params['default_message_type'], $course_config['default_message_type']);
        
        // check attributes that CANNOT be changed by a course (only changed at system level)
        $this->assertEquals($default_params['ferpa'], $course_config['ferpa']);
        $this->assertEquals($default_params['downloads'], $course_config['downloads']);
        $this->assertEquals($default_params['allow_mentor_copy'], $course_config['allow_mentor_copy']);
        $this->assertEquals($default_params['additionalemail'], $course_config['additionalemail']);
        $this->assertEquals($default_params['message_types_available'], $course_config['message_types_available']);
        $this->assertEquals($default_params['send_now_threshold'], $course_config['send_now_threshold']);
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
        // $this->assertNotEquals($default_params['default_message_type'], $course_config['default_message_type']);
        
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
        $this->assertEquals($default_params['allow_mentor_copy'], $course_config['allow_mentor_copy']);
        $this->assertEquals($default_params['additionalemail'], $course_config['additionalemail']);
        $this->assertEquals($default_params['message_types_available'], $course_config['message_types_available']);
        $this->assertEquals($default_params['send_now_threshold'], $course_config['send_now_threshold']);
    }

}