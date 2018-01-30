<?php

namespace block_quickmail\requests\transformers;

use block_quickmail_config;

class compose_transformer extends transformer {

    public function transform_form_data()
    {
        $this->transformed_data->subject = (string) $this->form_data->subject;
        $this->transformed_data->message = (string) $this->form_data->message_editor['text'];
        $this->transformed_data->mailto_ids = $this->get_transformed_mailto_ids();
        $this->transformed_data->additional_emails = $this->get_transformed_additional_emails();
        $this->transformed_data->signature_id = $this->get_transformed_signature_id();
        $this->transformed_data->message_type = $this->get_transformed_message_type();
        $this->transformed_data->receipt = (bool) $this->form_data->receipt;
        $this->transformed_data->alternate_email_id = $this->get_transformed_alternate_email_id();
        $this->transformed_data->to_send_at = $this->get_transformed_to_send_at();
        $this->transformed_data->attachments_draftitem_id = $this->get_transformed_attachments_draftitem_id();
        $this->transformed_data->no_reply = $this->get_transformed_no_reply();
    }

    /**
     * Returns a sanitized array of recipient user ids from the form post data
     * 
     * @return array
     */
    public function get_transformed_mailto_ids()
    {
        return empty($this->form_data->mailto_ids) ? [] : explode(',', rtrim($this->form_data->mailto_ids, ','));
    }

    /**
     * Returns a sanitized array of additional emails from the form post data
     * 
     * @return array
     */
    public function get_transformed_additional_emails()
    {
        $additional_emails = $this->form_data->additional_emails;

        $emails = ! empty($additional_emails) ? array_unique(explode(',', $additional_emails)) : [];

        // eliminate any white space
        $emails = array_map(function($email) {
            return trim($email);
        }, $emails);

        // return all valid emails
        return array_filter($emails, function($email) {
            return strlen($email) > 0;
        });
    }

    /**
     * Returns a sanitized signature id from the form post data
     * 
     * @return int
     */
    public function get_transformed_signature_id()
    {
        return ! $this->form_data->signature_id 
            ? 0 
            : (int) $this->form_data->signature_id;
    }

    /**
     * Returns a sanitized message type from the form post data
     * 
     * @return string
     */
    public function get_transformed_message_type()
    {
        return ! empty($this->form_data->message_type) 
            ? (string) $this->form_data->message_type 
            : block_quickmail_config::_c('default_message_type');
    }

    /**
     * Returns a sanitized alternate email id from the form post data
     * 
     * @return int
     */
    public function get_transformed_alternate_email_id()
    {
        return (int) $this->form_data->from_email_id > 0
            ? $this->form_data->from_email_id
            : 0;
    }

    /**
     * Returns a sanitized to send at timestamp from the form post data
     * 
     * @return int
     */
    public function get_transformed_to_send_at()
    {
        return ! $this->form_data->to_send_at 
            ? 0 
            : (int) $this->form_data->to_send_at;
    }

    /**
     * Returns ...
     * 
     * @return int
     */
    public function get_transformed_attachments_draftitem_id()
    {
        return ! $this->form_data->attachments ? 0 : (int) $this->form_data->attachments;
    }

    /**
     * Returns a sanitized no_reply value from the form post data
     * 
     * @return bool
     */
    public function get_transformed_no_reply()
    {
        return $this->form_data->from_email_id == -1
            ? true
            : false;
    }

}