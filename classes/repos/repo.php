<?php

namespace block_quickmail\repos;

use block_quickmail\repos\pagination\paginator;

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

    private function set_sort($params)
    {
        $this->sort = array_key_exists('sort', $params) && in_array($params['sort'], array_keys($this->sortable_attrs))
            ? $params['sort']
            : 'id';
    }

    private function set_dir($params)
    {
        $this->dir = array_key_exists('dir', $params) && in_array($params['dir'], ['asc', 'desc'])
            ? $params['dir']
            : 'asc';
    }

    private function set_paginate($params)
    {
        $this->paginate = array_key_exists('paginate', $params)
            ? $params['paginate']
            : false;
    }

    private function set_page($params)
    {
        $this->page = array_key_exists('page', $params) && is_int($params['page'] + 0)
            ? $params['page']
            : 1;
    }

    private function set_per_page($params)
    {
        $this->per_page = array_key_exists('per_page', $params)
            ? $params['per_page']
            : 10;
    }

    private function set_uri($params)
    {
        $this->uri = array_key_exists('uri', $params)
            ? $params['uri']
            : '';
    }

    private function set_result()
    {
        $this->result = (object) [
            'data' => [],
            'pagination' => (object) []
        ];
    }

    public function get_sort_column_name($key)
    {
        return $this->sortable_attrs[$key];
    }

    public function set_result_data($data = [])
    {
        $this->result->data = $data;
    }

    public function make_paginator($count)
    {
        $paginator = new paginator($count, $this->page, $this->per_page, $this->uri);

        return $paginator;
    }

}