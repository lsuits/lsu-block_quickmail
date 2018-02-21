<?php

namespace block_quickmail\repos\pagination;

use block_quickmail\repos\pagination\paginator;

/*
 * This class is a convenience pagination DTO which contains pagination results based
 * on the given paginator object. This object should be returned to repo objects in
 * order to set pagination output, as well as to provide a calculated offset to
 * query the data.
 */
class paginated {

    public $paginator;
    public $page_count;
    public $offset;
    public $per_page;
    public $current_page;
    public $next_page;
    public $previous_page;
    public $total_count;
    public $uri_for_page;
    public $first_page_uri;
    public $last_page_uri;
    public $next_page_uri;
    public $previous_page_uri;

    public function __construct(paginator $paginator) {
        $this->paginator = $paginator;
        $this->page_count = $this->page_count();
        $this->offset = $this->offset();
        $this->per_page = $this->per_page();
        $this->current_page = $this->current_page();
        $this->next_page = $this->next_page();
        $this->previous_page = $this->previous_page();
        $this->total_count = $this->total_count();
        $this->uri_for_page = $this->uri_for_page();
        $this->first_page_uri = $this->first_page_uri();
        $this->last_page_uri = $this->last_page_uri();
        $this->next_page_uri = $this->next_page_uri();
        $this->previous_page_uri = $this->previous_page_uri();
        unset($this->paginator); // dont need this anymore
    }

    /**
     * Returns the total page count
     * 
     * @return int
     */
    private function page_count()
    {
        return (int) $this->paginator->total_pages;
    }

    /**
     * Returns the calculated offset
     * 
     * @return int
     */
    private function offset()
    {
        return (int) $this->paginator->offset;
    }

    /**
     * Returns the "results per page" setting
     * 
     * @return int
     */
    private function per_page()
    {
        return (int) $this->paginator->per_page;
    }
    
    /**
     * Returns the current page number
     * 
     * @return int
     */
    private function current_page()
    {
        return (int) $this->paginator->page;
    }

    /**
     * Returns the next page number
     * 
     * @return int
     */
    private function next_page()
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
    private function previous_page()
    {
        return $this->current_page() == 1
            ? 1
            : $this->current_page() - 1;
    }
    
    /**
     * Returns the total number of results in the original data collection
     * 
     * @return int
     */
    private function total_count()
    {
        return (int) $this->paginator->total_count;
    }

    /**
     * Returns the uri for the current page
     * 
     * @param  int  $page  optional
     * @return string
     */
    private function uri_for_page($page = null)
    {
        // if no page was given, set at current page
        $page = is_null($page) ? $this->current_page() : $page;

        // normalize page number
        $page = $page < 1 ? 1 : $page;
        $page = $page > $this->page_count() ? $this->page_count() : $page;

        return $this->inject_page_in_uri($page);
    }
    
    /**
     * Returns the uri for the first page
     * 
     * @return string
     */
    private function first_page_uri()
    {
        return $this->inject_page_in_uri(1);
    }

    /**
     * Returns the uri for the last page
     * 
     * @return string
     */
    private function last_page_uri()
    {
        return $this->inject_page_in_uri($this->page_count());
    }

    /**
     * Returns the uri for the next page
     * 
     * @return string
     */
    private function next_page_uri()
    {
        return $this->inject_page_in_uri($this->next_page());
    }

    /**
     * Returns the uri for the previous page
     * 
     * @return string
     */
    private function previous_page_uri()
    {
        return $this->inject_page_in_uri($this->previous_page());
    }

    /**
     * Returns a preserved uri containing the given page number
     * 
     * @param  int  $page
     * @return string
     */
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

    /**
     * Reports whether or not this is the last page
     * 
     * @return bool
     */
    private function has_more_pages()
    {
        return $this->current_page() < $this->page_count();
    }

}