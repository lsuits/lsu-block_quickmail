<?php

class block_quickmail_cache {

    public static $name = 'block_quickmail';

    public $store;

    // qm_msg_recip_count
    // qm_msg_deliv_count

    public function __construct($store) {
        $this->store = $store;
    }

    /**
     * Instantiates and returns a quickmail cache instance for the given store name
     * 
     * @param  string  $store_name   provider name in block config
     * @return self
     */
    public static function store($store_name)
    {
        $store = self::get_cache_store($store_name);

        $instance = new self($store);

        return $instance;
    }

    /**
     * Returns the given key, or default value if missing
     * 
     * @param  string|int  $key
     * @param  mixed   $default   a default value to return, can also be a closure
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $this->store->get($key);

        // if missing (moodle returns false as no value)
        if ($value === false) {
            // and the default is a closure
            if (is_callable($default)) {
                // return closure
                return call_user_func($default);
            }

            // otherwise, default or null if no default given
            return $default === null ? null : $default;
        }

        return $value;
    }

    /**
     * Reports whether or not the given cache key exists in the store
     * 
     * @param  string|int  $key
     * @return bool
     */
    public function check($key)
    {
        $value = $this->get($key);

        return $value !== null;
    }

    /**
     * Stores a value in the cache only if an existing value does not exist
     * 
     * @param  string|int  $key
     * @param  mixed   $value   can be a closure
     * @return mixed
     */
    public function add($key, $value)
    {
        $existing_value = $this->get($key);

        if ($existing_value !== null) {
            return $existing_value;
        }

        $new_value = $this->put($key, $value);

        return $new_value;
    }

    /**
     * Stores a value in the cache, overriding the existing value if any exists
     * 
     * @param  string|int  $key
     * @param  mixed   $value   can be a closure
     * @return mixed
     */
    public function put($key, $value)
    {
        // if the value is a closure
        if (is_callable($value)) {
            $value = call_user_func($value);
        }

        $this->store->set($key, $value);

        return $value;
    }

    /**
     * Stores a value in the cache only if an existing value does not exist
     *
     * (Similar to add for now...)
     * 
     * @param  string|int  $key
     * @param  mixed   $value   can be a closure
     * @return mixed
     */
    public function remember($key, $value)
    {
        $existing = $this->get($key);

        if ($existing === null) {
            $existing = $this->put($key, $value);
        }

        return $existing;
    }

    /**
     * Fetches and then deletes an item from the cache
     * 
     * @param  string|int  $key
     * @return mixed
     */
    public function pull($key)
    {
        $value = $this->get($key);

        $this->forget($key);

        return $value;
    }

    /**
     * Deletes an item from the cache
     * 
     * @param  string|int  $key
     * @return bool  result of deletion
     */
    public function forget($key)
    {
        $result = $this->store->delete($key);

        return $result;
    }

    /**
     * Instantiates and returns a moodle cache instance for the given "store name" (provider name)
     * 
     * @param  string  $store_name   provider name in block config
     * @return cache object
     */
    public static function get_cache_store($store_name)
    {
        $cache_store = \cache::make(self::$name, $store_name);

        return $cache_store;
    }

}