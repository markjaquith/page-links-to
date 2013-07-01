<?php

class CWS_PLT_Test_Redirection extends CWS_PLT_TestCase {
	function test_get_permalink_filter() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		query_posts( array( 'p' => $post_id ) );
		$this->assertEquals( 'http://example.com/', $this->plugin()->get_redirect() );
	}
}
