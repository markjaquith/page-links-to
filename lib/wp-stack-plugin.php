<?php

// Convenience methods
if ( !class_exists( 'WP_Stack_Plugin' ) ) {
	class WP_Stack_Plugin {
		public function hook( $hook ) {
			$priority = 10;
			$method = $this->sanitize_method( $hook );
			$args = func_get_args();
			unset( $args[0] );
			foreach( (array) $args as $arg ) {
				if ( is_int( $arg ) )
					$priority = $arg;
				else
					$method = $arg;
			}
			return add_action( $hook, array( $this, $method ), $priority, 999 );
		}

		private function sanitize_method( $method ) {
			return str_replace( array( '.', '-' ), array( '_DOT_', '_DASH_' ), $method );
		}
	}
}
