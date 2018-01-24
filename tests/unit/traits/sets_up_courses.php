<?php

////////////////////////////////////////////////////
///
///  COURSE SET UP HELPERS
/// 
////////////////////////////////////////////////////

trait sets_up_courses {

    /**
     * Creates a course within a category with 1 teacher, 4 students
     * 
     * @return array  course, user_teacher, students[]
     */
    public function setup_course_with_teacher_and_students()
    {
        // create a course category
        $category = $this->getDataGenerator()->create_category();

        // create a course
        $course = $this->getDataGenerator()->create_course();

        // create a user (teacher)
        $user_teacher = $this->getDataGenerator()->create_user([
            'email' => 'teacher@example.com', 
            'username' => 'teacher'
        ]);

        // create a user (student1)
        $user_student1 = $this->getDataGenerator()->create_user([
            'email' => 'student1@example.com', 
            'username' => 'student1'
        ]);

        // create a user (student2)
        $user_student2 = $this->getDataGenerator()->create_user([
            'email' => 'student2@example.com', 
            'username' => 'student2'
        ]);

        // create a user (student3)
        $user_student3 = $this->getDataGenerator()->create_user([
            'email' => 'student3@example.com', 
            'username' => 'student3'
        ]);

        // create a user (student4)
        $user_student4 = $this->getDataGenerator()->create_user([
            'email' => 'student4@example.com', 
            'username' => 'student4'
        ]);

        // enrol the teacher in the course
        $this->getDataGenerator()->enrol_user($user_teacher->id, $course->id, 4, 'manual');

        // enrol the students in the course
        $this->getDataGenerator()->enrol_user($user_student1->id, $course->id, 5, 'manual');
        $this->getDataGenerator()->enrol_user($user_student2->id, $course->id, 5, 'manual');
        $this->getDataGenerator()->enrol_user($user_student3->id, $course->id, 5, 'manual');
        $this->getDataGenerator()->enrol_user($user_student4->id, $course->id, 5, 'manual');


        return [
            $course,
            $user_teacher,
            [
                $user_student1,
                $user_student2,
                $user_student3,
                $user_student4
            ]
        ];
    }

    /*
     * FOR SOME REASON THIS DOES NOT WORK !! :(
     */
    public function assign_configuration_to_course($course, $override_params)
    {
        global $DB, $CFG;

        $params = $this->get_course_config_params($override_params);

        $dataobjects = [];

        // iterate over each given param, inserting each record for this course
        foreach ($params as $name => $value) {
            $config = new \stdClass;
            $config->coursesid = $course->id;
            $config->name = $name;
            $config->value = $value;

            $dataobjects[] = $config;
        }

        $DB->insert_records('block_quickmail_config', $dataobjects);
    }

}