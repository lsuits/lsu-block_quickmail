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

    public function get_role_list()
    {
        $archetypes = get_role_archetypes();

        $results = [];
        $i = 1;

        foreach ($archetypes as $archetype) {
            $results[$i] = $archetype;
            $i++;
        }

        return $results;
    }

    // manager
    // coursecreator
    // editingteacher
    // teacher
    // student
    // guest
    // user
    // frontpage
    
    // returns [course, context, enrolled_users]
    public function setup_course_with_users($params = [])
    {
        // create a course category
        $category = $this->getDataGenerator()->create_category();

        // create a course
        $course = $this->getDataGenerator()->create_course();

        $roles = $this->get_role_list();
        
        // initialize user results container
        $enrolled_users = [];

        foreach($roles as $role_name) {
            $enrolled_users[$role_name] = [];
        }

        foreach ($roles as $role_id => $role_name) {
            if (array_key_exists($role_name, $params)) {
                foreach (range(1, $params[$role_name]) as $i) {
                    $handle = $role_name . $i;

                    // create a user
                    $user = $this->getDataGenerator()->create_user([
                        'email' => $handle . '@example.com', 
                        'username' => $handle
                    ]);

                    // enroll user in course
                    $this->getDataGenerator()->enrol_user($user->id, $course->id, $role_id, 'manual');

                    $enrolled_users[$role_name][] = $user;
                }
            }
        }

        return [
            $course,
            context_course::instance($course->id),
            $enrolled_users
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