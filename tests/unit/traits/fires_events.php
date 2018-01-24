<?php

////////////////////////////////////////////////////
///
///  EVENT HELPERS
/// 
////////////////////////////////////////////////////

trait fires_events {

    public function open_event_sink()
    {
        // $this->preventResetByRollback();
        
        $sink = $this->redirectEvents();

        return $sink;
    }

    public function close_event_sink($sink)
    {
        $sink->close();
    }

}