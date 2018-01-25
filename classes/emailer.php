<?php

/**
 * This class sends "one-off" emails from the given user with the given subject and body
 *
 * Either "to_email() or to_user()" MUST be called before sending
 */
class block_quickmail_emailer {

    public $from_user;
    public $subject;
    public $body;
    public $to_email;
    public $to_user;
    public $reply_to_email;
    public $reply_to_name;
    public $wordwrapwidth;

    /**
     * Construct an emailer
     * 
     * @param object  $from_user  the moodle user sending the email
     * @param string  $subject    email subject
     * @param string  $body       email body
     */
    public function __construct($from_user, $subject, $body) {
        $this->from_user = $from_user;
        $this->subject = $subject;
        $this->body = $body;
        $this->to_email = null;
        $this->to_user = null;
        $this->reply_to_email = get_config('moodle', 'noreplyaddress');
        $this->reply_to_name = get_config('moodle', 'noreplyaddress');
        $this->wordwrapwidth = 79;
    }

    /**
     * Sets the "to email" to the given (pre-validated) email
     * 
     * @param  string  $email
     * @return void
     */
    public function to_email($email)
    {
        $this->to_email = $email;
    }

    /**
     * Sets the "to user" to the given user
     * 
     * @param  object  $user
     * @return void
     */
    public function to_user($user)
    {
        $this->to_user = $user;
    }

    /**
     * Sets the reply to params to the given email and name
     * 
     * @param  string  $email
     * @param  string  $name
     * @return void
     */
    public function reply_to($email, $name)
    {
        $this->reply_to_email = $email;
        $this->reply_to_name = $name;
    }

    /**
     * Returns the recipient user which will either be the set "real" user, or a "fake" user
     * 
     * @return object
     */
    private function get_to_user()
    {
        return ! empty($this->to_user)
            ? $this->to_user
            : $this->get_fake_user();
    }

    /**
     * Returns a "fake" user object which should fit the needs of the moodle email function
     * 
     * @return object
     */
    private function get_fake_user()
    {
        $user = new \stdClass();
        $user->id = mt_rand(99999800, 99999999); // we have to pass an id
        $user->email = $this->to_email;
        $user->username = $this->to_email;
        $user->mailformat = 1; // @TODO - make this configurable??

        return $user;
    }

    /**
     * Send the email
     * 
     * @return bool success or no?
     */
    public function send()
    {
        $success = email_to_user(
            $this->get_to_user(),
            $this->from_user,
            $this->subject,
            format_text_email($this->body, 1),
            purify_html($this->body),
            '', // @TODO: make attachments happen!
            '', // @TODO: make attachments happen!
            true,
            $this->reply_to_email,
            $this->reply_to_name,
            $this->wordwrapwidth
        );

        return $success;
    }
    
}