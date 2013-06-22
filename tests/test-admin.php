<?php

class CWS_PLT_Test_Admin extends CWS_PLT_TestCase {
	function test_plugin_row_meta() {
		$metas = apply_filters( 'plugin_row_meta', array( 'one', 'two' ), plugin_basename( CWS_PageLinksTo::FILE ) );
		$this->assertEquals( 3, count( $metas ) );
		$this->assertContains( 'GitHub', array_pop( $metas ) );
	}
}
