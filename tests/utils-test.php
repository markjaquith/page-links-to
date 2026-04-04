<?php

class CWS_PLT_Test_Utils extends CWS_PLT_TestCase {
	public function test_clean_url(): void {
		foreach ( [
			'http://example.com/' => 'http://example.com/',
			'  http://example.com/  ' => 'http://example.com/',
			'www.example.com/' => 'http://www.example.com/',
			' www.example.com/' => 'http://www.example.com/',
		] as $in => $out ) {
			$this->assertEquals( $out, $this->plugin()->clean_url( $in ) );
		}
	}
}
