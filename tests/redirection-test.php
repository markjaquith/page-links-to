<?php

class CWS_PLT_Test_Redirection extends CWS_PLT_TestCase {
	public function test_get_permalink_filter(): void {
		$post_id = $this->factory->post->create( [ 'post_type' => 'post' ] );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/' ) );
		query_posts( [ 'p' => $post_id ] );
		$this->assertEquals( 'http://example.com/', $this->plugin()->get_redirect() );
	}

	public function test_host_relative_redirect(): void {
		$post_id = $this->factory->post->create( [ 'post_type' => 'post' ] );
		$this->plugin()->set_link( $post_id, '/foo' );
		query_posts( [ 'p' => $post_id ] );
		$this->assertEquals( get_option( 'home' ) . '/foo', $this->plugin()->get_redirect() );
	}

	public function test_protocol_relative_redirect(): void {
		$post_id = $this->factory->post->create( [ 'post_type' => 'post' ] );
		$this->plugin()->set_link( $post_id, '//example.com/foo' );
		query_posts( [ 'p' => $post_id ] );
		$this->assertEquals( (is_ssl() ? 'https:' : 'http:' ) . '//example.com/foo', $this->plugin()->get_redirect() );
	}

	public function test_redirection_with_asperand(): void {
		$post_id = $this->factory->post->create( [ 'post_type' => 'post' ] );
		$this->assertTrue( $this->plugin()->set_link( $post_id, 'http://example.com/@test' ) );
		query_posts( [ 'p' => $post_id ] );
		$this->assertEquals( 'http://example.com/%40test', $this->plugin()->get_redirect() );
	}
}
