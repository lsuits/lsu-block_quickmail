<?php
//
// Written at Louisiana State University
//
class Message {

    public  $subject,
            $text,
            $html,
            $users,
            $admins,
            $warnings,
            $noreply,
            $sentUsers,
            $startTime,
            $endTime;

    public function __construct($data, $users){
        global $DB;
        $this->warnings = array();

        $this->subject  = $data->subject;
        $this->html     = $data->message_editor['text'];
        $this->text     = strip_tags($data->message_editor['text']);
        $this->noreply  = $data->noreply;
        $this->warnings = array();
        $this->users    = array_values($DB->get_records_list('user', 'id', $users));
    }

    public function send($users = null){
        
        $this->startTime = time();
        $users = empty($users) ? $this->users : $users;

        $noreplyUser                = new stdClass();
        $noreplyUser->firstname     = 'Moodle';
        $noreplyUser->lastname      = 'Administrator';
        $noreplyUser->username      = 'moodleadmin';
        $noreplyUser->email         = $this->noreply;
        $noreplyUser->maildisplay   = 2;

        foreach($users as $user) {
            $success = email_to_user(
                    $user,          // to
                    $noreplyUser,   // from
                    $this->subject, // subj
                    $this->text,    // body in plain text
                    $this->html,    // body in HTML
                    '',             // attachment
                    '',             // attachment name
                    true,           // user true address ($USER)
                    $this->noreply, // reply-to address
                    get_string('pluginname', 'block_admin_email') // reply-to name
                    );
            if(!$success)
                $this->warnings[] = get_string('email_error', 'block_admin_email', $user);
            else{
                $this->sentUsers[] = $user->username;
            }
        }
        $this->endTime = time();
    }

    public function buildAdminReceipt(){
        global $CFG, $DB;
        $adminIds     = explode(',',$CFG->siteadmins);
        $this->admins = $DB->get_records_list('user', 'id',$adminIds);

        $usersLine  = sprintf("Message sent to %d/%d users.<br/>", count($this->sentUsers), count($this->users));
        $timeLine   = sprintf("Time elapsed: %d seconds<br/>", $this->endTime - $this->startTime);
        $warnline   = sprintf("Warnings: %d<br/>", count($this->warnings));
        $msgLine    = sprintf("message body as follows<br/><br/><hr/>%s<hr/>", $this->html);
        if(count($this->sentUsers) > 0) {
            $recipLine  = sprintf("sent successfully to the following users:<br/><br/>%s", implode(', ', $this->sentUsers));
        } else {
            $recipLine  = sprintf("It looks like you either have email sending disabled or things are very broken%s",NULL);
        }
        return $usersLine.$warnline.$timeLine.$msgLine.$recipLine;
    }

    public function sendAdminReceipt(){
        $this->html = $this->buildAdminReceipt();
        $this->text = $this->buildAdminReceipt();
        $this->subject = "Admin Email send receipt";
        $this->send($this->admins);
    }
}
