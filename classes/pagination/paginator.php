<?php

namespace block_quickmail\pagination;

use block_quickmail\pagination\paginated;

class paginator {

    public $results;
    public $page;
    public $per_page;
    public $page_uri;
    public $results_total;
    public $total_pages;
    public $sliced_results;

    public function __construct($results, $page = 1, $per_page = 10, $page_uri = '') {
        $this->results = $results;
        $this->page = $page;
        $this->per_page = $per_page;
        $this->page_uri = $page_uri;
        $this->set_results_total();
        $this->paginate();
    }

    public static function get_paginated($results, $page = 1, $per_page = 10, $page_uri = '')
    {
        $paginator = new self($results, $page, $per_page, $page_uri);

        return $paginator->paginated();
    }

    /**
     * Performs calculation and setting of all pagination details
     * 
     * @return void
     */
    public function paginate()
    {
        $this->validate_page_lower();

        $this->calculate_total_pages();

        $this->validate_page_upper();
        
        $offset = $this->calculate_offset();

        $this->sliced_results = array_slice($this->results, $offset, $this->per_page);
    }

    /**
     * Returns a paginated data object
     *
     * NOTE: must be run after paginate()
     * 
     * @return object
     */
    public function paginated()
    {
        return new paginated($this);
    }

    private function set_results_total()
    {
        $this->results_total = count($this->results);
    }

    /**
     * Sets page number to "1" if input index is less than 1
     * 
     * @return void
     */
    private function validate_page_lower()
    {
        $this->page = $this->page <= 0 
            ? 1 
            : $this->page;
    }

    /**
     * Sets calculated count of total pages based on set results and parameters
     * 
     * @return void
     */
    private function calculate_total_pages()
    {
        $this->total_pages = (int) ceil($this->results_total / $this->per_page);
    }

    /**
     * Sets page number to maximum possible page if set page exceeds total pages
     * 
     * @return void
     */
    private function validate_page_upper()
    {
        $this->page = min($this->page, $this->total_pages);
    }

    /**
     * Returns a calculated offset used to slice the results
     * 
     * @return int
     */
    private function calculate_offset()
    {
        $offset = ($this->page - 1) * $this->per_page;
        
        return $offset < 0 
            ? 0 
            : $offset;
    }

}