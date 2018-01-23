<?php

namespace block_quickmail\persistents\concerns;

use coding_exception;

trait can_be_soft_deleted {

    /**
     * Permanently deletes an entry from the database.
     *
     * @return bool True on success.
     */
    public function hard_delete() {
        $result = $this->delete();

        return $result;
    }

    /**
     * Updates an entry from the database to appear as if it has been deleted
     *
     * NOTE: this relies on core moodle persistent class functionality!!!
     * 
     * @return bool True on success.
     */
    public function soft_delete() {
        global $DB;

        if (empty($this->raw_get('id'))) {
            throw new coding_exception('id is required to delete');
        }

        // Hook before delete.
        $this->before_delete();

        $record = $this->to_record();
        $record = (array) $record;
        $record['timedeleted'] = time();

        // Save the record.
        $result = $DB->update_record(static::TABLE, $record);

        // Hook after delete.
        $this->after_delete($result);

        // refresh the model to reflect changes
        $this->read();

        return $result;
    }

}