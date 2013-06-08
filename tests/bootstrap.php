<?php

require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../page-links-to.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';

class CWS_PLT_TestCase extends WP_UnitTestCase {
	function plugin() {
		return CWS_PageLinksTo::$instance;
	}

	function set_post( $key, $value ) {
		$_POST[$key] = $_REQUEST[$key] = addslashes( $value );
	}

	function unset_post( $key ) {
		unset( $_POST[$key], $_REQUEST[$key] );
	}
}
