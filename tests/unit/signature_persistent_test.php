<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\persistents\signature;

class block_quickmail_signature_persistent_testcase extends advanced_testcase {
    
    use has_general_helpers;

    public function test_sets_default_when_no_user_signature_exists()
    {
        $this->resetAfterTest(true);

        $signature = signature::create_new([
            'user_id' => 1,
            'title' => 'first',
            'default_flag' => 0,
            'signature' => '<p>This is my signature!</p>',
        ]);

        $this->assertEquals(1, $signature->get('default_flag'));
        $this->assertTrue($signature->is_default());
    }
    
    public function test_changes_default_when_new_signature_is_created_as_default()
    {
        $this->resetAfterTest(true);

        // create (default) signature
        $signature1 = signature::create_new([
            'user_id' => 1,
            'title' => 'first',
            'default_flag' => 0,
            'signature' => '<p>This is my signature!</p>',
        ]);

        $this->assertTrue($signature1->is_default());

        // create new (default) signature
        $signature2 = signature::create_new([
            'user_id' => 1,
            'title' => 'second',
            'default_flag' => 1,
            'signature' => '<p>This is another signature!</p>',
        ]);

        // refresh the first signature
        $signature1->read();

        $this->assertTrue($signature2->is_default());
        $this->assertFalse($signature1->is_default());
    }

    public function test_makes_another_signature_default_when_default_is_deleted()
    {
        $this->resetAfterTest(true);

        // create (default) signature
        $signature1 = signature::create_new([
            'user_id' => 1,
            'title' => 'first',
            'default_flag' => 0,
            'signature' => '<p>This is my signature!</p>',
        ]);

        $this->assertTrue($signature1->is_default());

        // create new (non-default) signature
        $signature2 = signature::create_new([
            'user_id' => 1,
            'title' => 'second',
            'default_flag' => 0,
            'signature' => '<p>This is another signature!</p>',
        ]);

        $this->assertFalse($signature2->is_default());

        // delete the current default
        $signature1->delete();

        // refresh the second signature
        $signature2->read();

        $this->assertFalse($signature1->is_default());
        $this->assertTrue($signature2->is_default());
    }

    public function test_finds_default_signature_for_user()
    {
        $this->resetAfterTest(true);

        $signature1 = signature::create_new([
            'user_id' => 1,
            'title' => 'first',
            'signature' => '<p>This is my signature!</p>',
        ]);

        $signature2 = signature::create_new([
            'user_id' => 1,
            'title' => 'second',
            'signature' => '<p>This is another signature!</p>',
        ]);

        $signature3 = signature::create_new([
            'user_id' => 1,
            'title' => 'third',
            'signature' => '<p>This is yet another signature!</p>',
        ]);
        
        $default = signature::get_default_signature_for_user(1);

        $this->assertInstanceOf(signature::class, $default);
        $this->assertEquals($default->get('id'), $signature1->get('id'));
    }

    public function test_gets_signature_scoped_to_user()
    {
        $this->resetAfterTest(true);

        $signature1_1 = signature::create_new([
            'user_id' => 1,
            'title' => 'first',
            'signature' => '<p>This is my signature!</p>',
        ]);

        $signature1_2 = signature::create_new([
            'user_id' => 1,
            'title' => 'second',
            'signature' => '<p>This is another signature!</p>',
        ]);

        $signature2_1 = signature::create_new([
            'user_id' => 2,
            'title' => 'first',
            'signature' => '<p>This is my signature!</p>',
        ]);

        // get a signature belonging to the user
        $signature = signature::find_user_signature_or_null($signature1_1->get('id'), 1);

        $this->assertInstanceOf(signature::class, $signature);
        $this->assertEquals($signature->get('id'), $signature1_1->get('id'));

        // attempt to get a signature that belongs to a different user
        $signature = signature::find_user_signature_or_null($signature1_1->get('id'), 2);

        $this->assertNull($signature);
    }

    public function test_gets_user_signatures_as_array()
    {
        $this->resetAfterTest(true);

        $signature1 = signature::create_new([
            'user_id' => 1,
            'title' => 'first',
            'signature' => '<p>This is my signature!</p>',
        ]);

        $signature2 = signature::create_new([
            'user_id' => 1,
            'title' => 'second',
            'signature' => '<p>This is another signature!</p>',
        ]);

        $signature3 = signature::create_new([
            'user_id' => 1,
            'title' => 'third',
            'signature' => '<p>This is yet another signature!</p>',
        ]);

        $signatures = signature::get_flat_array_for_user(1);

        $this->assertInternalType('array', $signatures);
        $this->assertCount(3, $signatures);
    }

}