<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\messenger\messenger;
use block_quickmail\tasks\send_message_adhoc_task;
use core\task\manager as task_manager;

class block_quickmail_send_message_adhoc_task_testcase extends advanced_testcase {
    
    use has_general_helpers, 
        sets_up_courses, 
        submits_compose_message_form, 
        sends_emails, 
        sends_messages;
    
    public function test_send_message_adhoc_task_sends()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        $now = time();

        // get a compose form submission, sending message now
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
            'to_send_at' => $now
        ]);

        // schedule an email from the teacher to the students (as queued adhoc tasks)
        $message = messenger::compose($user_teacher, $course, $compose_form_data, null, true);

        \phpunit_util::run_all_adhoc_tasks();

        // should be no tasks fire yet, so no emails
        $this->assertEquals(0, $this->email_sink_email_count($sink));

        $task = new send_message_adhoc_task();

        $task->set_custom_data([
            'message_id' => $message->get('id')
        ]);

        // queue job
        task_manager::queue_adhoc_task($task);

        \phpunit_util::run_all_adhoc_tasks();

        // should have executed the taks, so 4 emails
        $this->assertEquals(4, $this->email_sink_email_count($sink));

        $this->close_email_sink($sink);
    }

}