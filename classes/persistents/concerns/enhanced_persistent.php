<?php

namespace block_quickmail\persistents\concerns;

use \dml_missing_record_exception;
use block_quickmail\persistents\concerns\can_be_soft_deleted;

trait enhanced_persistent {

    ///////////////////////////////////////////////
    ///
    ///  CUSTOM STATIC METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Finds this persistent's record by id number or returns null
     * 
     * @param  int  $id
     * @return object (persistent)|null
     * @throws dml_missing_record_exception
     */
    public static function find_or_null($id = 0)
    {
        // if no persistent id was passed, return null
        if ( ! $id) {
            return null;
        }

        // try to find and return model, otherwise return null
        try {
            $model = new self($id);

            // make sure this model has not been soft-deleted
            if (self::supports_soft_deletes()) {
                if ($model->get('timedeleted')) {
                    return null;
                }
            }

            return $model;
        } catch (dml_missing_record_exception $e) {
            return null;
        }
    }

    /**
     * Creates a new persistent record with the given array of attributes
     * 
     * @param  object  $data
     * @return object (persistent)
     * @throws dml_missing_record_exception
     */
    public static function create_new($data)
    {
        $model = new self(0, $data);

        $model->create();

        return $model;
    }

    /**
     * Reports whether or not this persistent can be soft deleted
     * 
     * @return bool
     */
    public static function supports_soft_deletes()
    {
        if ( ! $traits = class_uses(static::class)) {
            return false;
        }

        return in_array(can_be_soft_deleted::class, $traits);
    }

}