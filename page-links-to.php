<?php
/*
Plugin Name: Page Links To
Plugin URI: http://txfx.net/code/wordpress/page-links-to/
Description: Allows you to point WordPress pages or posts to a URL of your choosing.  Good for setting up navigational links to non-WP sections of your site or to off-site resources.
Version: 2.3
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

/*  Copyright 2005-2008  Mark Jaquith (email: mark.gpl@txfx.net)

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

// Compat functions for WP < 2.8
if ( !function_exists( 'esc_attr' ) ) {
	function esc_attr( $attr ) {
		return attribute_escape( $attr );
	}

	function esc_url( $url ) {
		return clean_url( $url );
	}
}

function txfx_get_post_meta_by_key( $key ) {
	global $wpdb;
	return $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $key ) );
}

function txfx_get_page_links_to_meta () {
	global $wpdb, $page_links_to_cache, $blog_id;

	if ( !isset( $page_links_to_cache[$blog_id] ) )
		$links_to = txfx_get_post_meta_by_key( '_links_to' );
	else
		return $page_links_to_cache[$blog_id];

	if ( !$links_to ) {
		$page_links_to_cache[$blog_id] = false;
		return false;
	}

	foreach ( (array) $links_to as $link )
		$page_links_to_cache[$blog_id][$link->post_id] = $link->meta_value;

	return $page_links_to_cache[$blog_id];
}

function txfx_get_page_links_to_targets () {
	global $wpdb, $page_links_to_target_cache, $blog_id;

	if ( !isset( $page_links_to_target_cache[$blog_id] ) )
		$links_to = txfx_get_post_meta_by_key( '_links_to_target' );
	else
		return $page_links_to_target_cache[$blog_id];

	if ( !$links_to ) {
		$page_links_to_target_cache[$blog_id] = false;
		return false;
	}

	foreach ( (array) $links_to as $link )
		$page_links_to_target_cache[$blog_id][$link->post_id] = $link->meta_value;

	return $page_links_to_target_cache[$blog_id];
}

function txfx_plt_add_meta_box( $page, $context ) {
	if ( ( 'page' === $page || 'post' === $page ) && 'advanced' === $context )
		add_meta_box( 'page-links-to', 'Page Links To', 'txfx_plt_meta_box', $page, 'advanced', 'low' );
}

function txfx_plt_meta_box() {
	global $post;
	echo '<p>';
	wp_nonce_field( 'txfx_plt', '_txfx_pl2_nonce', false, true );
	echo '</p>';
	$url = get_post_meta( $post->ID, '_links_to', true);
	if ( !$url )
		$url = 'http://';
?>
	<p>Point to this URL: <input name="txfx_links_to" type="text" style="width:75%" id="txfx_links_to" value="<?php echo attribute_escape( $url ); ?>" /></p>
	<p><label for="txfx_links_to_new_window"><input type="checkbox" name="txfx_links_to_new_window" id="txfx_links_to_new_window" value="_blank" <?php checked( '_blank', get_post_meta( $post->ID, '_links_to_target', true ) ); ?>> Open this link in a new window</label></p>
	<p><label for="txfx_links_to_302"><input type="checkbox" name="txfx_links_to_302" id="txfx_links_to_302" value="302" <?php checked( '302', get_post_meta( $post->ID, '_links_to_type', true ) ); ?>> Use a temporary <code>302</code> redirect (default is a permanent <code>301</code> redirect)</label></p>
<?php
}

function txfx_plt_save_meta_box( $post_ID ) {
	if ( wp_verify_nonce( $_REQUEST['_txfx_pl2_nonce'], 'txfx_plt' ) ) {
		if ( isset( $_POST['txfx_links_to'] ) && strlen( $_POST['txfx_links_to'] ) > 0 && $_POST['txfx_links_to'] !== 'http://' ) {
			$link = stripslashes( $_POST['txfx_links_to'] );
			if ( 0 === strpos( $link, 'www.' ) )
				$link = 'http://' . $link; // Starts with www., so add http://
			update_post_meta( $post_ID, '_links_to', $link );
			if ( isset( $_POST['txfx_links_to_new_window'] ) )
				update_post_meta( $post_ID, '_links_to_target', '_blank' );
			else
				delete_post_meta( $post_ID, '_links_to_target' );
			if ( isset( $_POST['txfx_links_to_302'] ) )
				update_post_meta( $post_ID, '_links_to_type', '302' );
			else
				delete_post_meta( $post_ID, '_links_to_type' );
		} else {
			delete_post_meta( $post_ID, '_links_to' );
			delete_post_meta( $post_ID, '_links_to_target' );
			delete_post_meta( $post_ID, '_links_to_type' );
		}
	}
	return $post_ID;
}


function txfx_filter_links_to_pages ($link, $post) {
	$page_links_to_cache = txfx_get_page_links_to_meta();

	// Really strange, but page_link gives us an ID and post_link gives us a post object
	$id = ( $post->ID ) ? $post->ID : $post;

	if ( $page_links_to_cache[$id] )
		$link = esc_url( $page_links_to_cache[$id] );

	return $link;
}

function txfx_redirect_links_to_pages() {
	if ( !is_single() && !is_page() )
		return;

	global $wp_query;

	$link = get_post_meta( $wp_query->post->ID, '_links_to', true );

	if ( !$link )
		return;

	$redirect_type = get_post_meta( $wp_query->post->ID, '_links_to_type', true );
	$redirect_type = ( $redirect_type = '302' ) ? '302' : '301';
	wp_redirect( $link, $redirect_type );
	exit;
}

function txfx_page_links_to_highlight_tabs( $pages ) {
	$page_links_to_cache = txfx_get_page_links_to_meta();
	$page_links_to_target_cache = txfx_get_page_links_to_targets();

	if ( !$page_links_to_cache && !$page_links_to_target_cache )
		return $pages;

	$this_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$targets = array();

	foreach ( (array) $page_links_to_cache as $id => $page ) {
		if ( isset( $page_links_to_target_cache[$id] ) )
			$targets[$page] = $page_links_to_target_cache[$id];

		if ( str_replace( 'http://www.', 'http://', $this_url ) == str_replace( 'http://www.', 'http://', $page ) || ( is_home() && str_replace( 'http://www.', 'http://', trailingslashit( get_bloginfo( 'home' ) ) ) == str_replace( 'http://www.', 'http://', trailingslashit( $page ) ) ) ) {
			$highlight = true;
			$current_page = esc_url( $page );
		}
	}

	if ( count( $targets ) ) {
		foreach ( $targets as  $p => $t ) {
			$p = esc_url( $p );
			$t = esc_attr( $t );
			$pages = str_replace( '<a href="' . $p . '" ', '<a href="' . $p . '" target="' . $t . '" ', $pages );
		}
	}

	if ( $highlight ) {
		$pages = preg_replace( '| class="([^"]+)current_page_item"|', ' class="$1"', $pages ); // Kill default highlighting
		$pages = preg_replace( '|<li class="([^"]+)"><a href="' . $current_page . '"|', '<li class="$1 current_page_item"><a href="' . $current_page . '"', $pages );
	}

	return $pages;
}

function txfx_plt_init() {
	if ( get_option( 'txfx_plt_schema_version' ) < 3 ) {
		global $wpdb;
		$wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = '_links_to'        WHERE meta_key = 'links_to'        " );
		$wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = '_links_to_target' WHERE meta_key = 'links_to_target' " );
		$wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = '_links_to_type'   WHERE meta_key = 'links_to_type'   " );
		wp_cache_flush();
		update_option( 'txfx_plt_schema_version', 3 );
	}
}

add_filter( 'wp_list_pages',     'txfx_page_links_to_highlight_tabs', 9     );
add_action( 'template_redirect', 'txfx_redirect_links_to_pages'             );
add_filter( 'page_link',         'txfx_filter_links_to_pages',        20, 2 );
add_filter( 'post_link',         'txfx_filter_links_to_pages',        20, 2 );
add_action( 'do_meta_boxes',     'txfx_plt_add_meta_box',             10, 2 );
add_action( 'save_post',         'txfx_plt_save_meta_box'                   );
add_action( 'init',              'txfx_plt_init'                            );
