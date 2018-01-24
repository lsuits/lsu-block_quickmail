<?php

////////////////////////////////////////////////////
///
///  EMAIL HELPERS
/// 
////////////////////////////////////////////////////

trait sends_emails {

    public function open_email_sink()
    {
        unset_config('noemailever');
        
        $sink = $this->redirectEmails();

        return $sink;
    }

    public function close_email_sink($sink)
    {
        $sink->close();
    }

    public function email_sink_email_count($sink)
    {
        return count($sink->get_messages());
    }

    // subject
    // from
    // to
    public function email_in_sink_attr($sink, $index, $attr)
    {
        $messages = $sink->get_messages();

        $message = $messages[$index - 1];

        return $message->$attr;
    }

    public function email_in_sink_body_contains($sink, $index, $body_text)
    {
        $messages = $sink->get_messages();

        $message = $messages[$index - 1];

        $body = $message->body;

        return (bool) strpos($body, format_text_email($body_text, 1)); // <--- is this cheating? hmm...
    }


}