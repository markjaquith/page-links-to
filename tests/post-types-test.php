<?php

class CWS_PLT_Test_Post_Types extends CWS_PLT_TestCase {
	public function test_get_default_post_types_includes_show_ui_types_and_omits_manual_exclusions(): void {
		register_post_type( 'plt_queryless_ui', array(
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
		) );

		register_post_type( 'plt_no_ui', array(
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => false,
		) );

		$wp_help_registered_by_test = post_type_exists( 'wp-help' );
		if ( ! $wp_help_registered_by_test ) {
			register_post_type( 'wp-help', array(
				'public'  => true,
				'show_ui' => true,
			) );
		}

		$wp_block_registered_by_test = post_type_exists( 'wp_block' );
		if ( ! $wp_block_registered_by_test ) {
			register_post_type( 'wp_block', array(
				'public'  => true,
				'show_ui' => true,
			) );
		}

		try {
			$post_types = CWS_PageLinksTo::get_default_post_types();

			$this->assertTrue( in_array( 'page', $post_types, true ) );
			$this->assertTrue( in_array( 'plt_queryless_ui', $post_types, true ) );
			$this->assertFalse( in_array( 'plt_no_ui', $post_types, true ) );
			$this->assertFalse( in_array( 'wp-help', $post_types, true ) );
			$this->assertFalse( in_array( 'wp_block', $post_types, true ) );
		} finally {
			unregister_post_type( 'plt_queryless_ui' );
			unregister_post_type( 'plt_no_ui' );

			if ( ! $wp_help_registered_by_test ) {
				unregister_post_type( 'wp-help' );
			}

			if ( ! $wp_block_registered_by_test ) {
				unregister_post_type( 'wp_block' );
			}
		}
	}
}
