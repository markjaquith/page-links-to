<?php

class CWS_PLT_Test_Utils extends CWS_PLT_TestCase {
	function test_clean_url() {
		foreach ( array(
			'http://example.com/' => 'http://example.com/',
			'  http://example.com/  ' => 'http://example.com/',
			'www.example.com/' => 'http://www.example.com/',
			' www.example.com/' => 'http://www.example.com/',
		) as $in => $out ) {
			$this->assertEquals( $out, $this->plugin()->clean_url( $in ) );
		}
	}

	function test_inline_coffeescript() {
		$this->assertNotContains( 'CoffeeScript', $this->plugin()->inline_coffeescript( 'js/new-tab.js' ) );
	}
}
