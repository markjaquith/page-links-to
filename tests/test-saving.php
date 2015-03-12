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

	function test_updating_attachment() {
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );
		$post_id = $this->factory->post->create( array( 'post_type' => 'attachment', 'post_author' => $user_id ) );

		$this->set_post( '_cws_plt_nonce', wp_create_nonce( 'cws_plt_' . $post_id ) );

		// example.org in same window
		$this->set_post( 'cws_links_to_choice', 'custom' );
		$this->set_post( 'cws_links_to', 'http://example.org/' );
		$this->unset_post( 'cws_links_to_new_tab' );
		$this->plugin()->edit_attachment( $post_id );
		$this->assertEquals( 'http://example.org/', $this->plugin()->get_link( $post_id ) );
	}

	function test_updating_post() {
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );
		$post_id = $this->factory->post->create( array( 'post_type' => 'post', 'post_author' => $user_id ) );

		$this->set_post( '_cws_plt_nonce', wp_create_nonce( 'cws_plt_' . $post_id ) );

		// example.org in same window
		$this->set_post( 'cws_links_to_choice', 'custom' );
		$this->set_post( 'cws_links_to', 'http://example.org/' );
		$this->unset_post( 'cws_links_to_new_tab' );
		$this->plugin()->save_post( $post_id );
		$this->assertEquals( 'http://example.org/', $this->plugin()->get_link( $post_id ) );
		$this->assertFalse( $this->plugin()->get_target( $post_id ) );

		// example.com in new window
		$this->set_post( 'cws_links_to_choice', 'custom' );
		$this->set_post( 'cws_links_to', 'http://example.com/' );
		$this->set_post( 'cws_links_to_new_tab', '_blank' );
		$this->plugin()->save_post( $post_id );
		$this->assertEquals( 'http://example.com/', $this->plugin()->get_link( $post_id ) );
		$this->assertTrue( $this->plugin()->get_target( $post_id ) );

		// WP link selected
		$this->set_post( 'cws_links_to_choice', 'wp' );
		$this->set_post( 'cws_links_to', 'http://example.com/' );
		$this->set_post( 'cws_links_to_new_tab', '_blank' );
		$this->plugin()->save_post( $post_id );
		$this->assertFalse( $this->plugin()->get_link( $post_id ) );
		$this->assertFalse( $this->plugin()->get_target( $post_id ) );

		// No radio selected, but link provided
		$this->unset_post( 'cws_links_to_choice' );
		$this->set_post( 'cws_links_to', 'http://example.com/link-no-radio' );
		$this->set_post( 'cws_links_to_new_tab', '_blank' );
		$this->plugin()->save_post( $post_id );
		$this->assertEquals( 'http://example.com/link-no-radio', $this->plugin()->get_link( $post_id ) );
		$this->assertTrue( $this->plugin()->get_target( $post_id ) );

		// New post
		$post_id = $this->factory->post->create( array( 'post_type' => 'post', 'post_author' => $user_id ) );

		// Nonce missing
		$this->unset_post( '_cws_plt_nonce' );
		$this->set_post( 'cws_links_to_choice', 'custom' );
		$this->set_post( 'cws_links_to', 'http://example.com/nonce-test' );
		$this->set_post( 'cws_links_to_new_tab', '_blank' );
		$this->plugin()->save_post( $post_id );
		$this->assertFalse( $this->plugin()->get_link( $post_id ) );
		$this->assertFalse( $this->plugin()->get_target( $post_id ) );

		// Nonce wrong
		$this->set_post( '_cws_plt_nonce', wp_create_nonce( 'WRONG_INPUT' ) );
		$this->plugin()->save_post( $post_id );
		$this->assertFalse( $this->plugin()->get_link( $post_id ) );
		$this->assertFalse( $this->plugin()->get_target( $post_id ) );
	}
}
