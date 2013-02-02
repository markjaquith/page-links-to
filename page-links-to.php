<?php
/*
Plugin Name: Page Links To
Plugin URI: http://txfx.net/wordpress-plugins/page-links-to/
Description: Allows you to point WordPress pages or posts to a URL of your choosing.  Good for setting up navigational links to non-WP sections of your site or to off-site resources.
Version: 2.8
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

/*  Copyright 2005-2013  Mark Jaquith

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class CWS_PageLinksTo {
	static $instance;
	var $targets;
	var $links;
	var $targets_on_this_page;

	function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Bootstraps the upgrade process and registers all the hooks.
	 */
	function init() {
		$this->maybe_upgrade();

		load_plugin_textdomain( 'page-links-to', false, basename( dirname( __FILE__ ) ) . '/languages' );

		add_filter( 'wp_list_pages',       array( $this, 'wp_list_pages'       )        );
		add_action( 'template_redirect',   array( $this, 'template_redirect'   )        );
		add_filter( 'page_link',           array( $this, 'link'                ), 20, 2 );
		add_filter( 'post_link',           array( $this, 'link'                ), 20, 2 );
		add_filter( 'post_type_link',      array( $this, 'link',               ), 20, 2 );
		add_action( 'do_meta_boxes',       array( $this, 'do_meta_boxes'       ), 20, 2 );
		add_action( 'save_post',           array( $this, 'save_post'           )        );
		add_filter( 'wp_nav_menu_objects', array( $this, 'wp_nav_menu_objects' ), 10, 2 );
		add_action( 'load-post.php',       array( $this, 'load_post'           )        );
		add_filter( 'the_posts',           array( $this, 'the_posts'           )        );
	}

 /**
  * Performs an upgrade for older versions. Hides the keys so they only show in the plugin's UI
  */
	function maybe_upgrade() {
		if ( get_option( 'txfx_plt_schema_version' ) < 3 ) {
			global $wpdb;
			$wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = '_links_to'        WHERE meta_key = 'links_to'        " );
			$wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = '_links_to_target' WHERE meta_key = 'links_to_target' " );
			$wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = '_links_to_type'   WHERE meta_key = 'links_to_type'   " );
			wp_cache_flush();
			update_option( 'txfx_plt_schema_version', 3 );
		}
	}

	/**
	 * Returns post ids and meta values that have a given key
	 * @param string $key post meta key
	 * @return array an array of objects with post_id and meta_value properties
	 */
	function meta_by_key( $key ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $key ) );
	}

	/**
	 * Returns all links for the current site
	 * @return array an array of links, keyed by post ID
	 */
	function get_links() {
		global $wpdb, $blog_id;

		if ( !isset( $this->links[$blog_id] ) )
			$links_to = $this->meta_by_key( '_links_to' );
		else
			return $this->links[$blog_id];

		if ( !$links_to ) {
			$this->links[$blog_id] = false;
			return false;
		}

		foreach ( (array) $links_to as $link )
			$this->links[$blog_id][$link->post_id] = $link->meta_value;

		return $this->links[$blog_id];
	}

	/**
	 * Returns all targets for the current site
	 * @return array an array of targets, keyed by post ID
	 */
	function get_targets () {
		global $wpdb, $page_links_to_target_cache, $blog_id;

		if ( !isset( $this->targets[$blog_id] ) )
			$links_to = $this->meta_by_key( '_links_to_target' );
		else
			return $this->targets[$blog_id];

		if ( !$links_to ) {
			$this->targets[$blog_id] = false;
			return false;
		}

		foreach ( (array) $links_to as $link )
			$this->targets[$blog_id][$link->post_id] = $link->meta_value;

		return $this->targets[$blog_id];
	}

	/**
	 * Adds the meta box to the post or page edit screen
	 * @param string $page the name of the current page
	 * @param string $context the current context
	 */
	function do_meta_boxes( $page, $context ) {
		// Plugins that use custom post types can use this filter to hide the PLT UI in their post type.
		$plt_post_types = apply_filters( 'page-links-to-post-types', array_keys( get_post_types( array('show_ui' => true ) ) ) );

		if ( in_array( $page, $plt_post_types ) && 'advanced' === $context )
			add_meta_box( 'page-links-to', 'Page Links To', array( $this, 'meta_box' ), $page, 'advanced', 'low' );
	}

	function meta_box() {
		global $post;
		echo '<p>';
		wp_nonce_field( 'txfx_plt', '_txfx_pl2_nonce', false, true );
		echo '</p>';
		$url = get_post_meta( $post->ID, '_links_to', true);
		if ( !$url ) {
			$linked = false;
			$url = 'http://';
		} else {
			$linked = true;
		}
	?>
		<p><?php _e( 'Point this content to:', 'page-links-to' ); ?></p>
		<p><label><input type="radio" id="txfx-links-to-choose-wp" name="txfx_links_to_choice" value="wp" <?php checked( !$linked ); ?> /> <?php _e( 'Its normal WordPress URL', 'page-links-to' ); ?></label></p>
		<p><label><input type="radio" id="txfx-links-to-choose-alternate" name="txfx_links_to_choice" value="alternate" <?php checked( $linked ); ?> /> <?php _e( 'An alternate URL', 'page-links-to' ); ?></label></p>
		<div style="margin-left: 30px;" id="txfx-links-to-alternate-section" class="<?php echo !$linked ? 'hide-if-js' : ''; ?>">
			<p><input name="txfx_links_to" type="text" style="width:75%" id="txfx-links-to" value="<?php echo esc_attr( $url ); ?>" /></p>
			<p><label for="txfx-links-to-new-window"><input type="checkbox" name="txfx_links_to_new_window" id="txfx-links-to-new-window" value="_blank" <?php checked( '_blank', get_post_meta( $post->ID, '_links_to_target', true ) ); ?>> <?php _e( 'Open this link in a new window', 'page-links-to' ); ?></label></p>
		</div>
		<script src="<?php echo trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/page-links-to.dev.js'; ?>"></script>
	<?php
	}

	/**
	 * Saves data on post save
	 * @param int $post_ID a post ID
	 * @return int the post ID that was passed in
	 */
	function save_post( $post_ID ) {
		if ( isset( $_REQUEST['_txfx_pl2_nonce'] ) && wp_verify_nonce( $_REQUEST['_txfx_pl2_nonce'], 'txfx_plt' ) ) {
			if ( ( !isset( $_POST['txfx_links_to_choice'] ) || 'alternate' == $_POST['txfx_links_to_choice'] ) && isset( $_POST['txfx_links_to'] ) && strlen( $_POST['txfx_links_to'] ) > 0 && $_POST['txfx_links_to'] !== 'http://' ) {
				$link = trim( stripslashes( $_POST['txfx_links_to'] ) );
				if ( 0 === strpos( $link, 'www.' ) )
					$link = 'http://' . $link; // Starts with www., so add http://
				update_post_meta( $post_ID, '_links_to', $link );
				if ( isset( $_POST['txfx_links_to_new_window'] ) )
					update_post_meta( $post_ID, '_links_to_target', '_blank' );
				else
					delete_post_meta( $post_ID, '_links_to_target' );
			} else {
				delete_post_meta( $post_ID, '_links_to' );
				delete_post_meta( $post_ID, '_links_to_target' );
				delete_post_meta( $post_ID, '_links_to_type' );
			}
		}
		return $post_ID;
	}

	/**
	 * Filter for post or page links
	 * @param string $link the URL for the post or page
	 * @param int|object $post Either a post ID or a post object
	 * @return string output URL
	 */
	function link( $link, $post ) {
		$links = $this->get_links();

		// Really strange, but page_link gives us an ID and post_link gives us a post object
		$id = ( is_object( $post ) && $post->ID ) ? $post->ID : $post;

		if ( isset( $links[$id] ) && $links[$id] )
			$link = esc_url( $links[$id] );

		return $link;
	}

	/**
	 * Performs a redirect, if appropriate
	 */
	function template_redirect() {
		if ( !is_single() && !is_page() )
			return;

		global $wp_query;

		$link = get_post_meta( $wp_query->post->ID, '_links_to', true );

		if ( !$link )
			return;

		wp_redirect( $link, 301 );
		exit;
	}

	/**
	 * Filters the list of pages to alter the links and targets
	 * @param string $pages the wp_list_pages() HTML block from WordPress
	 * @return string the modified HTML block
	 */
	function wp_list_pages( $pages ) {
		$highlight = false;
		$links = $this->get_links();
		$page_links_to_target_cache = $this->get_targets();

		if ( !$links && !$page_links_to_target_cache )
			return $pages;

		$this_url = ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$targets = array();

		foreach ( (array) $links as $id => $page ) {
			if ( isset( $page_links_to_target_cache[$id] ) )
				$targets[$page] = $page_links_to_target_cache[$id];

			if ( str_replace( 'http://www.', 'http://', $this_url ) == str_replace( 'http://www.', 'http://', $page ) || ( is_home() && str_replace( 'http://www.', 'http://', trailingslashit( get_bloginfo( 'url' ) ) ) == str_replace( 'http://www.', 'http://', trailingslashit( $page ) ) ) ) {
				$highlight = true;
				$current_page = esc_url( $page );
			}
		}

		if ( count( $targets ) ) {
			foreach ( $targets as  $p => $t ) {
				$p = esc_url( $p );
				$t = esc_attr( $t );
				$pages = str_replace( '<a href="' . $p . '"', '<a href="' . $p . '" target="' . $t . '"', $pages );
			}
		}

		if ( $highlight ) {
			$pages = preg_replace( '| class="([^"]+)current_page_item"|', ' class="$1"', $pages ); // Kill default highlighting
			$pages = preg_replace( '|<li class="([^"]+)"><a href="' . preg_quote( $current_page ) . '"|', '<li class="$1 current_page_item"><a href="' . $current_page . '"', $pages );
		}
		return $pages;
	}

	function wp_nav_menu_objects( $items, $args ) {
		$page_links_to_target_cache = $this->get_targets();
		$new_items = array();
		foreach ( $items as $item ) {
			if ( isset( $page_links_to_target_cache[$item->object_id] ) )
				$item->target = $page_links_to_target_cache[$item->object_id];
			$new_items[] = $item;
		}
		return $new_items;
	}

	function load_post() {
		if ( isset( $_GET['post'] ) ) {
			if ( get_post_meta( absint( $_GET['post'] ), '_links_to', true ) ) {
				add_action( 'admin_notices', array( $this, 'notify_of_external_link' ) );
			}
		}
	}

	function notify_of_external_link() {
		?><div class="updated"><p><?php _e( '<strong>Note</strong>: This content is pointing to an alternate URL. Use the &#8220;Page Links To&#8221; box to change this behavior.', 'page-links-to' ); ?></p></div><?php
	}

	function id_to_url_callback( &$val, $key ) {
		$val = get_permalink( $val );
	}

	function the_posts( $posts ) {
		$page_links_to_target_cache = $this->get_targets();
		if ( is_array( $page_links_to_target_cache) && count( $page_links_to_target_cache ) ) {
			$pids = array();
			foreach ( (array) $posts as $p )
				$pids[$p->ID] = $p->ID;
			$targets = array_keys( array_intersect_key( $page_links_to_target_cache, $pids ) );
			if ( count( $targets ) ) {
				array_walk( $targets, array( $this, 'id_to_url_callback' ) );
				$targets = array_unique( $targets );
				$this->targets_on_this_page = $targets;
				wp_enqueue_script( 'jquery' );
				add_action( 'wp_head', array( $this, 'targets_in_new_window_via_js' ) );
			}
		}
		return $posts;
	}

	function targets_in_new_window_via_js() {
		?><script>(function($){var t=<?php echo json_encode( $this->targets_on_this_page ); ?>;$(document).ready(function(){var a=$('a');$.each(t,function(i,v){a.filter('[href="'+v+'"]').attr('target','_blank');});});})(jQuery);</script><?php
	}

}

new CWS_PageLinksTo;
