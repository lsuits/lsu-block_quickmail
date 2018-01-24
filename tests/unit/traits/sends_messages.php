<?php

////////////////////////////////////////////////////
///
///  MESSAGE HELPERS
/// 
////////////////////////////////////////////////////

trait sends_messages {

    public function open_message_sink()
    {
        $this->preventResetByRollback();
        
        $sink = $this->redirectMessages();

        return $sink;
    }

    public function close_message_sink($sink)
    {
        $sink->close();
    }

    public function message_sink_message_count($sink)
    {
        return count($sink->get_messages());
    }

}