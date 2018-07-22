<?php
defined( 'WPINC' ) or die;

/**
 * Returns the original URL of the post.
 *
 * @param null|int|WP_Post $post The post to fetch.
 * @return string The post's original URL.
 */
function plt_get_original_permalink( $post = null ) {
	return CWS_PageLinksTo::get_instance()->original_link( $post );
}
