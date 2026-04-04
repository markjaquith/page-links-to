<?php

class CWS_PLT_Test_Filtering extends CWS_PLT_TestCase {
	public function test_get_permalink_filter(): void {
		$post_id = $this->factory->post->create( [ 'post_type' => 'post' ] );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertEquals( 'http://example.com/', get_permalink( $post_id ) );
	}

	public function test_get_original_permalink(): void {
		$post_id = $this->factory->post->create( [ 'post_type' => 'post' ] );
		$original_permalink = get_permalink( $post_id );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertEquals( 'http://example.com/', get_permalink( $post_id ) );
		$this->assertEquals( $original_permalink, plt_get_original_permalink( $post_id ) );
	}

	public function test_wp_list_posts_filter(): void {
		$post_id = $this->factory->post->create( [ 'post_type' => 'page' ] );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertTrue( $this->plugin()->set_link_new_tab( $post_id ) );
		ob_start();
		wp_list_pages();
		$wp_list_pages = ob_get_clean();
		$this->assertStringContainsString( '#new_tab', $wp_list_pages );
	}

	public function test_nav_menu_items_filter(): void {
		$post_id = $this->factory->post->create( [ 'post_type' => 'page', 'post_status' => 'publish' ] );
		$post = get_post( $post_id );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertTrue( $this->plugin()->set_link_new_tab( $post_id ) );

		// Need a user with sufficient permissions because wp_insert_post() is not low level enough — WTF?
		$user_id = $this->factory->user->create( [ 'role' => 'editor' ] );
		wp_set_current_user( $user_id );
		$menu_id = wp_create_nav_menu( 'plt' );
		$this->assertIsInt( $menu_id, "Menu creation failed" );
		$item_id = wp_update_nav_menu_item( $menu_id, 0, [
			'menu-item-object-id' => $post_id,
			'menu-item-object' => $post->post_type,
			'menu-item-type' => 'post_type',
			'menu-item-status' => 'publish',
		]);
		$wp_nav_menu = wp_nav_menu( [ 'echo' => false, 'menu' => $menu_id, 'fallback_cb' => false ] );
		$this->assertIsString( $wp_nav_menu, 'Menu is empty' );
		$this->assertStringContainsString( 'target="_blank"', $wp_nav_menu );
	}
}
