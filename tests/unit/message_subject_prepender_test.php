<?php
 
require_once(dirname(__FILE__) . '/unit_testcase_traits.php');

class block_quickmail_message_subject_prepender_testcase extends advanced_testcase {
    
    use unit_testcase_has_general_helpers;
    use unit_testcase_sets_up_courses;

    public function test_format_course_subject_with_no_setting()
    {
        $this->resetAfterTest(true);
 
        $course = $this->getDataGenerator()->create_course();

        $subject = 'Hello world!';

        $formatted_subject = \block_quickmail\messenger\message_subject_prepender::format_course_subject($course, $subject);

        $this->assertEquals('Hello world!', $formatted_subject);
    }

    public function test_format_course_subject_with_idnumber_setting()
    {
        $this->resetAfterTest(true);
 
        $course = $this->getDataGenerator()->create_course();

        $subject = 'Hello world!';

        $this->update_system_config_value('block_quickmail_prepend_class', 'idnumber');
        
        $formatted_subject = \block_quickmail\messenger\message_subject_prepender::format_course_subject($course, $subject);

        $this->assertEquals('[' . $course->idnumber . '] Hello world!', $formatted_subject);
    }

    public function test_format_course_subject_with_shortname_setting()
    {
        $this->resetAfterTest(true);
 
        $course = $this->getDataGenerator()->create_course();

        $subject = 'Hello world!';

        $this->update_system_config_value('block_quickmail_prepend_class', 'shortname');
        
        $formatted_subject = \block_quickmail\messenger\message_subject_prepender::format_course_subject($course, $subject);

        $this->assertEquals('[' . $course->shortname . '] Hello world!', $formatted_subject);
    }

    public function test_format_course_subject_with_fullname_setting()
    {
        $this->resetAfterTest(true);
 
        $course = $this->getDataGenerator()->create_course();

        $subject = 'Hello world!';

        $this->update_system_config_value('block_quickmail_prepend_class', 'fullname');
        
        $formatted_subject = \block_quickmail\messenger\message_subject_prepender::format_course_subject($course, $subject);

        $this->assertEquals('[' . $course->fullname . '] Hello world!', $formatted_subject);
    }

}