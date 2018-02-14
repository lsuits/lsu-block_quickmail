<?php

namespace block_quickmail\pagination;

use block_quickmail\pagination\paginator;

class paginated {

    public $paginator;

    public function __construct(paginator $paginator) {
        $this->paginator = $paginator;
    }

    /**
     * Returns the paginated results
     * 
     * @return mixed
     */
    public function results()
    {
        return $this->paginator->sliced_results;
    }

    /**
     * Returns the total page count
     * 
     * @return int
     */
    public function page_count()
    {
        return (int) $this->paginator->total_pages;
    }

    /**
     * Returns the "results per page" setting
     * 
     * @return int
     */
    public function per_page()
    {
        return (int) $this->paginator->per_page;
    }
    
    /**
     * Returns the current page number
     * 
     * @return int
     */
    public function current_page()
    {
        return (int) $this->paginator->page;
    }

    /**
     * Returns the next page number
     * 
     * @return int
     */
    public function next_page()
    {
        return ! $this->has_more_pages()
            ? $this->current_page()
            : $this->current_page() + 1;
    }

    /**
     * Returns the previous page number
     * 
     * @return int
     */
    public function previous_page()
    {
        return $this->current_page() == 1
            ? 1
            : $this->current_page() - 1;
    }

    /**
     * Reports whether or not this is the last page
     * 
     * @return bool
     */
    public function has_more_pages()
    {
        return $this->current_page() < $this->page_count();
    }
    
    /**
     * Returns the total number of results in the original collection
     * 
     * @return int
     */
    public function total_results()
    {
        return (int) $this->paginator->results_total;
    }

    public function uri_for_page($page = null)
    {
        // if no page was given, set at current page
        $page = is_null($page) ? $this->current_page() : $page;

        // normalize page number
        $page = $page < 1 ? 1 : $page;
        $page = $page > $this->page_count() ? $this->page_count() : $page;

        return $this->inject_page_in_uri($page);
    }

    private function inject_page_in_uri($page)
    {
        $current_uri = $this->paginator->page_uri;

        $url = strstr($current_uri, '?', true);

        // get pure query string from uri
        $query_string = substr(strstr($current_uri, '?'), 1);

        // explode query string into associative array
        parse_str($query_string, $exploded_query_string);

        $exploded_query_string['page'] = $page;

        return $url . '?' . http_build_query($exploded_query_string, '', '&');
    }

    public function first_page_uri()
    {
        return $this->inject_page_in_uri(1);
    }

    public function last_page_uri()
    {
        return $this->inject_page_in_uri($this->page_count());
    }

    public function next_page_uri()
    {
        return $this->inject_page_in_uri($this->next_page());
    }

    public function previous_page_uri()
    {
        return $this->inject_page_in_uri($this->previous_page());
    }

    /**
     * Returns the paginator's set uri
     * 
     * @return string
     */
    private function get_page_uri()
    {
        return $this->paginator->page_uri;
    }

}