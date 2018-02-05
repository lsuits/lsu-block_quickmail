<?php

    /**
     * Sends a confirmation email to this alternate email's user given a specific "landing" course id
     * 
     * @param  int  $course_id
     * @return bool
     */
    public function send_confirmation_email($course_id) {
        // get this alternate's user
        $user = $this->get_setup_user();

        // generate, or fetch existing, token for this user and alternate instance
        // note: this does not expire!
        $token = get_user_key('blocks/quickmail', $user->id, $this->get('id'));

        // build the confirmation url for the alternate email set up user
        $approval_url = new moodle_url('/blocks/quickmail/alternate.php', [
            'courseid' => $course_id,
            'confirmid' => $this->get('id'), 
            'token' => $token
        ]);

        // construct the confirmation email content
        $a = (object)[];
        $a->email = $this->get('email');
        $a->url = html_writer::link($approval_url, $approval_url->out());
        $a->plugin_name = \block_quickmail_plugin::_s('pluginname');
        $a->fullname = fullname($user);
        $html_body = \block_quickmail_plugin::_s('alternate_body', $a);
        $body = strip_tags($html_body);

        // modify user details for this specific send
        $user->email = $this->get('email');
        $user->firstname = \block_quickmail_plugin::_s('pluginname');
        $user->lastname = \block_quickmail_plugin::_s('alternate');

        // send email
        $result = email_to_user(
            $user, 
            \block_quickmail_plugin::_s('alternate_from'), 
            \block_quickmail_plugin::_s('alternate_subject'), 
            $body, 
            $html_body
        );

        return $result;
    }

    /**
     * Confirms (validates) a specific alternate email given the requesting user and applicable parameters
     * 
     * @param  mdl_user  $user
     * @param  array     $params   required keys: courseid,confirmid,token
     * @return alternate_email
     * @throws \Exception
     */
    public static function confirm($user, array $params)
    {
        global $DB;

        // confirm all required parameters are given
        if (array_diff_key(array_flip(['courseid', 'confirmid', 'token']), $params)) {
            throw new \Exception(\block_quickmail_plugin::_s('alternate_confirm_invalid_params'));
        }

        // fetch alternate email record
        if ( ! $alternate_email = self::find_or_null($params['confirmid'])) {
            throw new \Exception(\block_quickmail_plugin::_s('alternate_no_record'));
        }

        // make sure this record is not already confirmed
        if ($alternate_email->get('is_validated')) {
            throw new \Exception(\block_quickmail_plugin::_s('alternate_confirm_already'));
        }

        // fetch the user key from the token
        if ( ! $key = $DB->get_record('user_private_key', [
            'instance' => $alternate_email->get('id'),
            'value' => $params['token'],
            'userid' => $user->id,
            'script' => 'blocks/quickmail'
        ])) {
            throw new \Exception(\block_quickmail_plugin::_s('alternate_confirm_invalid_token'));
        }

        // mark this alternate email as validated
        $alternate_email->set('is_validated', 1);
        $alternate_email->update();

        // delete the key
        $DB->delete_records('user_private_key', ['id' => $key->id]);

        return $alternate_email;
    }