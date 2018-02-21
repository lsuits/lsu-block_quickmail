<?php

namespace block_quickmail\repos\pagination;

use block_quickmail\repos\pagination\paginated;

class paginator {

    public $total_count;
    public $page;
    public $per_page;
    public $page_uri;
    public $total_pages;
    public $offset;

    public function __construct($total_count, $page = 1, $per_page = 10, $page_uri = '') {
        $this->total_count = $total_count;
        $this->page = $page;
        $this->per_page = $per_page;
        $this->page_uri = $page_uri;
        $this->set_page_lower();
        $this->set_total_pages();
        $this->set_page_upper();
        $this->set_offset();
    }

    /**
     * Returns a paginated data object
     *
     * @return object
     */
    public function paginated()
    {
        return new paginated($this);
    }

    /**
     * Sets page number to "1" if input index is less than 1
     * 
     * @return void
     */
    private function set_page_lower()
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
    private function set_total_pages()
    {
        $this->total_pages = (int) ceil($this->total_count / $this->per_page);
    }

    /**
     * Sets page number to maximum possible page if set page exceeds total pages
     * 
     * @return void
     */
    private function set_page_upper()
    {
        $this->page = min($this->page, $this->total_pages);
    }

    /**
     * Sets a calculated offset used to slice the results
     * 
     * @return int
     */
    private function set_offset()
    {
        $offset = ($this->page - 1) * $this->per_page;
        
        $this->offset = $offset < 0 
            ? 0 
            : $offset;
    }

}