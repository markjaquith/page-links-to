<?php

class CWS_PLT_Test_Filtering extends CWS_PLT_TestCase {
	function test_get_permalink_filter() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertEquals( 'http://example.com/', get_permalink( $post_id ) );
	}

	function test_wp_list_posts_filter() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertTrue( $this->plugin()->set_link_new_tab( $post_id ) );
		ob_start();
		wp_list_pages();
		$wp_list_pages = ob_get_clean();
		$this->assertContains( 'target="_blank"', $wp_list_pages );
	}

	function test_nav_menu_items_filter() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'page', 'post_status' => 'publish' ) );
		$post = get_post( $post_id );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertTrue( $this->plugin()->set_link_new_tab( $post_id ) );

		// Need a user with sufficient permissions because wp_insert_post() is not low level enough — WTF?
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );
		$menu_id = wp_create_nav_menu( 'plt' );
		$this->assertInternalType( 'int', $menu_id, "Menu creation failed" );
		$item_id = wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-object-id' => $post_id,
			'menu-item-object' => $post->post_type,
			'menu-item-type' => 'post_type',
			'menu-item-status' => 'publish',
		));
		$wp_nav_menu = wp_nav_menu( array( 'echo' => false, 'menu' => $menu_id, 'fallback_cb' => false ) );
		$this->assertInternalType( 'string', $wp_nav_menu, 'Menu is empty' );
		$this->assertContains( 'target="_blank"', $wp_nav_menu );
	}
}
