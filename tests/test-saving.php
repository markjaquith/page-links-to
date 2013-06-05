<?php

class CWS_PLT_Test_Saving extends CWS_PLT_TestCase {
	function test_setting_and_updating_url() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertEquals( 'http://example.com/', $this->plugin()->get_link( $post_id ) );
		// This update changes nothing, so should return false
		$this->assertFalse( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/updated' ) );
		$this->assertEquals( 'http://example.com/updated', $this->plugin()->get_link( $post_id ) );
	}
}
