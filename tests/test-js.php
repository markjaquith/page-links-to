<?php

class CWS_PLT_Test_JS extends CWS_PLT_TestCase {
	function test_footer_js() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertTrue( $this->plugin()->set_link_new_tab( $post_id ) );
		$this->assertEquals( 'http://example.com/', get_permalink( $post_id ) );
		ob_start();
		$this->plugin()->targets_in_new_window_via_js_footer();
		$footer_js = ob_get_clean();
		$this->assertContains( 'example.com', $footer_js );
	}
}
