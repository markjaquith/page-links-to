<?php
/*
Plugin Name: Page Links To
Plugin URI: http://txfx.net/code/wordpress/page-links-to/
Description: Allows you to set a "links_to" meta key with a URI value that will be be used when listing WP pages.  Good for setting up navigational links to non-WP sections of your 
Version: 1.0
Author URI: http://txfx.net/
*/

/*
=== INSTRUCTIONS ===
1) upload this file to /wp-content/plugins/
2) activate this plugin in the WordPress interface
3) create a new page with a title of your choosing, and with the parent page of your choosing.  Leave the content of the page blank.
4) add a meta key "links_to" with a full URI value (like "http://google.com/") (obviously without the quotes)

That's it!  Now, when you use wp_list_page(), that page should link to the "links_to" value, instead of its page
*/ 

function txfx_get_page_links_to_meta () {
	global $wpdb, $page_links_to_cache;

	if (!isset($page_links_to_cache)) {
	
		$links_to = $wpdb->get_results(
		"SELECT	post_id, meta_value " .
		"FROM $wpdb->postmeta, $wpdb->posts " .
		"WHERE post_id = ID AND meta_key = 'links_to' AND post_status = 'static'");
		} else {
			return $page_links_to_cache;
		}
		
		if (!$links_to) {
			$page_links_to_cache = false;
			return false;
		}
		
		foreach ($links_to as $link) {
		$page_links_to_cache[$link->post_id] = $link->meta_value;	
		}
		
		return $page_links_to_cache;
	}

function txfx_filter_links_to_pages ($link, $page_id) {
	$page_links_to_cache = txfx_get_page_links_to_meta();
	
	if ( $page_links_to_cache[$page_id] )
		$link = $page_links_to_cache[$page_id];

	return $link;
}

add_filter('page_link', 'txfx_filter_links_to_pages', 10, 2);
?>