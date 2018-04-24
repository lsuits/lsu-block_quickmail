<?php

////////////////////////////////////////////////////
///
///  MENTOR ASSIGNMENT HELPERS
/// 
////////////////////////////////////////////////////

trait assigns_mentors {

    public function create_mentor_role()
    {
        $mentor_role_id = $this->getDataGenerator()->create_role([
            'shortname' => 'mentor', 
            'name' => 'Mentor', 
            'description' => 'you shall pass', 
            // 'archetype' => ''
        ]);

        global $DB;

        $capabilities = [
            'moodle/user:editprofile',
            'moodle/user:readuserblogs',
            'moodle/user:readuserposts',
            'moodle/user:viewalldetails',
            'moodle/user:viewuseractivitiesreport',
            'moodle/user:viewdetails',
        ];

        foreach ($capabilities as $capability) {
            $record = (object)[];
            $record->contextid = 1;
            $record->roleid = $mentor_role_id;
            $record->capability = $capability;
            $record->permission = 1;
            $record->timemodified = time();
            $record->modifierid = 2;

            $DB->insert_record('role_capabilities', $record, false, false);
        }

        return $mentor_role_id;
    }

    public function create_mentor()
    {
        $mentor_role_id = $this->create_mentor_role();

        $mentor_user = $this->getDataGenerator()->create_user();

        $assignment_id = $this->getDataGenerator()->role_assign($mentor_role_id, $mentor_user->id);

        return [$mentor_user, $mentor_role_id];
    }

    public function create_mentor_for_user($user)
    {
        list($mentor, $mentor_role_id) = $this->create_mentor();

        $this->assign_mentor_to_mentee($mentor_role_id, $mentor, $user);

        return $mentor;
    }

    public function assign_mentor_to_mentee($mentor_role_id, $mentor, $mentee)
    {
        $assignment_id = $this->getDataGenerator()->role_assign($mentor_role_id, $mentor->id, context_user::instance($mentee->id)->id);

        return $assignment_id;
    }

}
