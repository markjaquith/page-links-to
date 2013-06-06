<?php

class CWS_PLT_Test_Saving extends CWS_PLT_TestCase {
	function test_get_links() {
		$this->assertEquals( array(), $this->plugin()->get_links() );
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertEquals( array( $post_id => 'http://example.com/' ), $this->plugin()->get_links() );
		$this->assertTrue( $this->plugin()->delete_link( $post_id ) );
		$this->assertEquals( array(), $this->plugin()->get_links() );
	}

	function test_get_targets() {
		$this->assertEquals( array(), $this->plugin()->get_targets() );
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertTrue( $this->plugin()->set_link_new_tab( $post_id ) );
		$this->assertEquals( array( $post_id => true ), $this->plugin()->get_targets() );
		$this->assertTrue( $this->plugin()->set_link_same_tab( $post_id ) );
		$this->assertEquals( array(), $this->plugin()->get_targets() );
	}

	function test_setting_and_updating_url() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertEquals( 'http://example.com/', $this->plugin()->get_link( $post_id ) );
		// This update changes nothing, so should return false
		$this->assertFalse( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/updated' ) );
		$this->assertEquals( 'http://example.com/updated', $this->plugin()->get_link( $post_id ) );
	}

	function test_deleting_url() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertTrue( $this->plugin()->delete_link( $post_id ) );
		$this->assertFalse( $this->plugin()->get_link( $post_id ) );
	}

	function test_setting_new_tab() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertFalse( $this->plugin()->get_target( $post_id ) );
		$this->assertTrue( $this->plugin()->set_link_new_tab( $post_id ) );
		$this->assertTrue( $this->plugin()->get_target( $post_id ) );
		// This update changes nothing, so should return false
		$this->assertFalse( $this->plugin()->set_link_new_tab( $post_id ) );
		$this->assertTrue( $this->plugin()->set_link_same_tab( $post_id ) );
		// This update changes nothing, so should return false
		$this->assertFalse( $this->plugin()->set_link_same_tab( $post_id ) );
		$this->assertFalse( $this->plugin()->get_target( $post_id ) );
	}

	function test_deleting_url_also_deletes_target() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertTrue( $this->plugin()->set_link_new_tab( $post_id ) );
		$this->assertTrue( $this->plugin()->get_target( $post_id ) );
		$this->assertTrue( $this->plugin()->delete_link( $post_id ) );
		$this->assertFalse( $this->plugin()->get_target( $post_id ) );
	}
}
