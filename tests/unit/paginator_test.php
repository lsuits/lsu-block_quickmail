<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\repos\pagination\paginator;
use block_quickmail\repos\pagination\paginated;

class block_quickmail_paginator_testcase extends advanced_testcase {
    
    use has_general_helpers;

    public function test_paginates_results_scenario_one()
    {
        $this->resetAfterTest(true);

        $results = $this->get_results(25);
        $uri = '/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc';

        $paginator = new paginator($results, 1, 10, $uri);

        $this->assertInternalType('array', $paginator->results);
        $this->assertCount(25, $paginator->results);
        $this->assertEquals(1, $paginator->page);
        $this->assertEquals(10, $paginator->per_page);
        $this->assertEquals($uri, $paginator->page_uri);
        $this->assertEquals(25, $paginator->results_total);
        $this->assertEquals(3, $paginator->total_pages);
        $this->assertInternalType('array', $paginator->sliced_results);
        $this->assertCount(10, $paginator->sliced_results);
    }

    public function test_paginates_results_scenario_two()
    {
        $this->resetAfterTest(true);

        $results = $this->get_results(121);
        $uri = '/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc';

        $paginator = new paginator($results, 4, 7, $uri);

        $this->assertInternalType('array', $paginator->results);
        $this->assertCount(121, $paginator->results);
        $this->assertEquals(4, $paginator->page);
        $this->assertEquals(7, $paginator->per_page);
        $this->assertEquals($uri, $paginator->page_uri);
        $this->assertEquals(121, $paginator->results_total);
        $this->assertEquals(18, $paginator->total_pages);
        $this->assertInternalType('array', $paginator->sliced_results);
        $this->assertCount(7, $paginator->sliced_results);
    }

    public function test_paginates_results_scenario_three()
    {
        $this->resetAfterTest(true);

        $results = $this->get_results(8);
        $uri = '/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc';

        $paginator = new paginator($results, 1, 11, $uri);

        $this->assertInternalType('array', $paginator->results);
        $this->assertCount(8, $paginator->results);
        $this->assertEquals(1, $paginator->page);
        $this->assertEquals(11, $paginator->per_page);
        $this->assertEquals($uri, $paginator->page_uri);
        $this->assertEquals(8, $paginator->results_total);
        $this->assertEquals(1, $paginator->total_pages);
        $this->assertInternalType('array', $paginator->sliced_results);
        $this->assertCount(8, $paginator->sliced_results);
    }

    public function test_adjusts_page_if_lower_than_one()
    {
        $this->resetAfterTest(true);

        $results = $this->get_results(8);
        $uri = '/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc';

        $paginator = new paginator($results, -4, 11, $uri);

        $this->assertEquals(1, $paginator->page);
    }

    public function test_adjusts_page_if_higher_than_appropriate()
    {
        $this->resetAfterTest(true);

        $results = $this->get_results(12);
        $uri = '/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc';

        $paginator = new paginator($results, 8, 11, $uri);

        $this->assertEquals(2, $paginator->page);
    }

    public function test_get_paginated_returns_paginated_object()
    {
        $this->resetAfterTest(true);

        $results = $this->get_results(25);
        $uri = '/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc';

        $paginated = paginator::get_paginated($results, 1, 10, $uri);

        $this->assertInstanceOf(paginated::class, $paginated);
    }

    public function test_paginated_scenario_one()
    {
        $this->resetAfterTest(true);

        $results = $this->get_results(25);
        $uri = '/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc';

        $paginated = paginator::get_paginated($results, 1, 10, $uri);

        $this->assertInternalType('array', $paginated->results());
        $this->assertCount(10, $paginated->results());
        $this->assertEquals(3, $paginated->page_count());
        $this->assertEquals(10, $paginated->per_page());
        $this->assertEquals(1, $paginated->current_page());
        $this->assertEquals(2, $paginated->next_page());
        $this->assertEquals(1, $paginated->current_page());
        $this->assertTrue($paginated->has_more_pages());
        $this->assertEquals(25, $paginated->total_results());
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=1', $paginated->uri_for_page());
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=2', $paginated->uri_for_page(2));
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=3', $paginated->uri_for_page(4));
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=1', $paginated->first_page_uri());
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=3', $paginated->last_page_uri());
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=2', $paginated->next_page_uri());
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=1', $paginated->previous_page_uri());
    }

    ////////////////////////////////////////
    
    private function get_results($number)
    {
        $results = [];

        foreach(range(1, $number) as $i) {
            $results[] = $i;
        }

        return $results;
    }

}