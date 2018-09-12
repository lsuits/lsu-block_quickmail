<?php

namespace block_quickmail\controllers\support;

use block_quickmail_cache;

class controller_session {

    public $controller_key;
    public $store;

    public static $cache_store = 'qm_controller_store';

    public function __construct($controller_key) {
        $this->controller_key = $controller_key;
        $this->store = block_quickmail_cache::store(self::$cache_store);
    }
    
    /**
     * Merges the given array of data into this controller session's currently set data
     * 
     * @param array $data
     * @return void
     */
    public function add_data($data = [])
    {
        $current = $this->get_data();

        $this->store->put($this->controller_key, array_merge($current, $data));
    }

    /**
     * Returns this controller session's currently set data
     * 
     * @param  string  $key  optional, a specific key within the store to return
     * @return mixed
     */
    public function get_data($key = null)
    {
        $data = $this->store->get($this->controller_key, []);

        if (empty($key)) {
            return $data;
        } else {
            return array_key_exists($key, $data) ? $data[$key] : null;
        }
    }

    /**
     * Reports whether or not the given key exists in the current session input data
     * 
     * @param  string  $key
     * @return bool
     */
    public function has_data($key)
    {
        return in_array($key, array_keys($this->store->get($this->controller_key, [])));
    }

    /**
     * Removes the given key's value from current session input data if it exists
     * 
     * @param  string  $key
     * @return void
     */
    public function forget_data($key)
    {
        $current = $this->get_data();

        if (array_key_exists($key, $current)) {
            unset($current[$key]);
        }

        $this->store->put($this->controller_key, $current);
    }

    /**
     * Deletes this controller session's currently set data
     * 
     * @return void
     */
    public function clear()
    {
        $this->store->forget($this->controller_key);
    }

    /**
     * Deletes this controller session's currently set data, and then resets it
     * 
     * @return void
     */
    public function reflash()
    {
        // get the current session data for this controller session
        $current = $this->get_data();

        // clear the session data for this controller session
        $this->clear();

        // if there was any data, add it back to the session
        if ( ! empty($current)) {
            $this->add_data($current);
        }
    }

}
