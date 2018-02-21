<?php

namespace block_quickmail\repos;

use block_quickmail\repos\pagination\paginator;
use block_quickmail\repos\pagination\paginated;

abstract class repo {

    public $sort;
    public $dir;
    public $paginate;
    public $page;
    public $per_page;
    public $uri;
    public $result;

    public function __construct($params = []) {
        $this->set_sort($params);
        $this->set_dir($params);
        $this->set_paginate($params);
        $this->set_page($params);
        $this->set_per_page($params);
        $this->set_uri($params);
        $this->set_result();
    }

    /**
     * Sets the sort field parameter (default to "id")
     * 
     * @param array $params
     */
    private function set_sort($params)
    {
        $this->sort = array_key_exists('sort', $params) && in_array($params['sort'], array_keys($this->sortable_attrs))
            ? $params['sort']
            : 'id';
    }

    /**
     * Sets the sort direction parameter (default to asc)
     * 
     * @param array $params
     */
    private function set_dir($params)
    {
        $this->dir = array_key_exists('dir', $params) && in_array($params['dir'], ['asc', 'desc'])
            ? $params['dir']
            : 'asc';
    }

    /**
     * Sets the pagination flag parameter, default to false (no pagination)
     * 
     * @param array $params
     */
    private function set_paginate($params)
    {
        $this->paginate = array_key_exists('paginate', $params)
            ? $params['paginate']
            : false;
    }

    /**
     * Sets the current page number parameter, default to 1
     * 
     * @param array $params
     */
    private function set_page($params)
    {
        $this->page = array_key_exists('page', $params) && is_int($params['page'] + 0)
            ? $params['page']
            : 1;
    }

    /**
     * Sets the sort field parameter, default to 10
     * 
     * @param array $params
     */
    private function set_per_page($params)
    {
        $this->per_page = array_key_exists('per_page', $params)
            ? $params['per_page']
            : 10;
    }

    /**
     * Sets the uri parameter, default to empty
     * 
     * @param array $params
     */
    private function set_uri($params)
    {
        $this->uri = array_key_exists('uri', $params)
            ? $params['uri']
            : '';
    }

    /**
     * Returns the database column name to sort by, given the "sortable_attr" key
     * 
     * @param  string  $key
     * @return string
     */
    public function get_sort_column_name($key)
    {
        return $this->sortable_attrs[$key];
    }

    /**
     * Sets the initial result object parameter (to be filled later)
     */
    private function set_result()
    {
        $this->result = (object) [
            'data' => [],
            'pagination' => (object) []
        ];
    }

    /**
     * Sets the data property on the result object
     * 
     * @param  array  $data   collection of data to be set
     * @return void
     */
    public function set_result_data($data = [])
    {
        $this->result->data = $data;
    }

    /**
     * Sets the pagination property on the result object
     * 
     * @param  paginated  $paginated  the paginated object created by the paginator
     * @return void
     */
    public function set_result_pagination(paginated $paginated)
    {
        $this->result->pagination = $paginated;
    }
    
    /**
     * Returns a paginated object for the result given a total count of records
     * 
     * @param  int  $count
     * @return paginated
     */
    public function get_paginated($count)
    {
        $paginator = $this->make_paginator($count);
        
        $paginated = $paginator->paginated();

        return $paginated;
    }

    /**
     * Instantiates and returns a paginator object given a total count of records
     * 
     * @param  int  $count
     * @return paginator
     */
    private function make_paginator($count)
    {
        $paginator = new paginator($count, $this->page, $this->per_page, $this->uri);

        return $paginator;
    }

}