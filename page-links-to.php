<?php
/**
 * The Page Links To plugin class.
 *
 * @package PageLinks
 *
 * Plugin Name: Page Links To
 * Plugin URI: http://txfx.net/wordpress-plugins/page-links-to/
 * Description: Allows you to point WordPress pages or posts to a URL of your choosing.  Good for setting up navigational links to non-WP sections of your site or to off-site resources.
 * Version: 2.11.1
 * Author: Mark Jaquith
 * Author URI: https://coveredweb.com/
 * Text Domain: page-links-to
 * Domain Path: /languages
 */

/*
Copyright 2005-2018  Mark Jaquith

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

/**
 * The Page Links To class.
 */
class CWS_PageLinksTo {
	/**
	 * The class instance.
	 *
	 * @var CWS_PageLinksTo
	 */
	static $instance;

	const LINKS_CACHE_KEY = 'plt_cache__links';
	const TARGETS_CACHE_KEY = 'plt_cache__targets';
	const LINK_META_KEY = '_links_to';
	const TARGET_META_KEY = '_links_to_target';
	const VERSION_KEY = 'txfx_plt_schema_version';
	const DISMISSED_NOTICES = 'page_links_dismissed_options';
	const MESSAGE_ID = 3;
	const SURVEY_URL = 'https://goo.gl/forms/8sTKH0LjPCCqBlrG2';
	const FILE = __FILE__;
	const CSS_JS_VERSION = '2.11.1';

	/**
	 * Whether to replace WP links with their specified URLs.
	 *
	 * @var bool
	 */
	protected $replace = true;

	/**
	 * Class constructor. Adds init hook.
	 */
	function __construct() {
		self::$instance = $this;
		$this->hook( 'init' );
	}

	/**
	 * Get the plugin instance.
	 *
	 * @return CWS_PageLinksTo The plugin class instance.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = $this;
		}

		return self::$instance;
	}

	/**
	 * Add a WordPress hook (action/filter).
	 *
	 * @param mixed $hook first parameter is the name of the hook. If second or third parameters are included, they will be used as a priority (if an integer) or as a class method callback name (if a string).
	 */
	public function hook( $hook ) {
		$priority = 10;
		$method = self::sanitize_method( $hook );
		$args = func_get_args();
		unset( $args[0] );
		foreach ( (array) $args as $arg ) {
			if ( is_int( $arg ) ) {
				$priority = $arg;
			} else {
				$method = $arg;
			}
		}

		return add_action( $hook, array( $this, $method ), $priority, 999 );
	}

	/**
	 * Sanitizes method names with bad characters.
	 *
	 * @param string $method The raw method name.
	 * @return string The sanitized method name.
	 */
	private static function sanitize_method( $method ) {
		return str_replace( array( '.', '-' ), array( '_DOT_', '_DASH_' ), $method );
	}

	/**
	 * Bootstraps the upgrade process and registers all the hooks.
	 */
	function init() {
		// Check to see if any of our data needs to be upgraded.
		$this->maybe_upgrade();

		// Load translation files.
		load_plugin_textdomain( 'page-links-to', false, basename( dirname( self::FILE ) ) . '/languages' );

		// Register hooks.
		$this->register_hooks();
	}

	/**
	 * Registers all the hooks.
	 *
	 * @return void
	 */
	function register_hooks() {
		// Hook in to URL generation.
		$this->hook( 'page_link',       'link', 20 );
		$this->hook( 'post_link',       'link', 20 );
		$this->hook( 'post_type_link',  'link', 20 );
		$this->hook( 'attachment_link', 'link', 20 );

		// Non-standard priority hooks.
		$this->hook( 'do_meta_boxes', 20 );
		$this->hook( 'wp_enqueue_scripts' );

		// Non-standard callback hooks.
		$this->hook( 'load-post.php', 'load_post' );
		$this->hook( 'wp_ajax_plt_dismiss_notice', 'ajax_dismiss_notice' );

		// Standard hooks.
		$this->hook( 'wp_list_pages' );
		$this->hook( 'template_redirect' );
		$this->hook( 'save_post' );
		$this->hook( 'edit_attachment' );
		$this->hook( 'wp_nav_menu_objects' );
		$this->hook( 'plugin_row_meta' );

		// Notices.
		if ( self::should_display_message() ) {
			$this->hook( 'admin_notices', 'notify_generic' );
		}

		// Metadata validation grants users editing privileges for our custom fields.
		register_meta( 'post', self::LINK_META_KEY,   null, '__return_true' );
		register_meta( 'post', self::TARGET_META_KEY, null, '__return_true' );
	}

	/**
	 * Performs an upgrade for older versions
	 *
	 * * Version 3: Underscores the keys so they only show in the plugin's UI.
	 */
	function maybe_upgrade() {
		// In earlier versions, the meta keys were stored without a leading underscore.
		// Since then, underscore has been codified as the standard for "something manages this" post meta.
		if ( get_option( self::VERSION_KEY ) < 3 ) {
			global $wpdb;
			$total_affected = 0;
			foreach ( array( '', '_target', '_type' ) as $meta_key ) {
				$meta_key = 'links_to' . $meta_key;
				$affected = $wpdb->update( $wpdb->postmeta, array(
					'meta_key' => '_' . $meta_key,
				), compact( 'meta_key' ) );
				if ( $affected ) {
					$total_affected += $affected;
				}
			}
			// Only flush the cache if something changed.
			if ( $total_affected > 0 ) {
				wp_cache_flush();
			}
			if ( update_option( self::VERSION_KEY, 3 ) ) {
				self::flush_links_cache();
				self::flush_targets_cache();
			}
		}
	}

	/**
	 * Enqueues frontend scripts.
	 */
	function wp_enqueue_scripts() {
		wp_enqueue_script( 'page-links-to', self::get_url() . 'js/new-tab.min.js', array(), self::CSS_JS_VERSION, true );
	}

	/**
	 * Returns post ids and meta values that have a given key.
	 *
	 * @param string $key post meta key.
	 * @return array|false objects with post_id and meta_value properties.
	 */
	public static function meta_by_key( $key ) {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $key ) );
	}

	/**
	 * Returns a single piece of post meta.
	 *
	 * @param  integer $post_id a post ID.
	 * @param  string  $key a post meta key.
	 * @return string|false the post meta, or false, if it doesn't exist.
	 */
	public static function get_post_meta( $post_id, $key ) {
		$meta = get_post_meta( absint( $post_id ), $key, true );

		if ( '' === $meta ) {
			return false;
		}

		return $meta;
	}

	/**
	 * Returns all links for the current site.
	 *
	 * @return array an array of links, keyed by post ID.
	 */
	public static function get_links() {
		$links = get_transient( self::LINKS_CACHE_KEY );

		if ( false === $links ) {
			$db_links = self::meta_by_key( self::LINK_META_KEY );
			$links = array();

			if ( $db_links ) {
				foreach ( $db_links as $link ) {
					$links[ intval( $link->post_id ) ] = $link->meta_value;
				}
			}

			set_transient( self::LINKS_CACHE_KEY, $links, 10 * 60 );
		}

		return $links;
	}

	/**
	 * Returns the link for the specified post ID.
	 *
	 * @param  integer $post_id a post ID.
	 * @return mixed either a URL or false.
	 */
	public static function get_link( $post_id ) {
		return self::get_post_meta( $post_id, self::LINK_META_KEY );
	}

	/**
	 * Returns all targets for the current site.
	 *
	 * @return array an array of targets, keyed by post ID.
	 */
	public static function get_targets() {
		$targets = get_transient( self::TARGETS_CACHE_KEY );

		if ( false === $targets ) {
			$db_targets = self::meta_by_key( self::TARGET_META_KEY );
			$targets = array();

			if ( $db_targets ) {
				foreach ( $db_targets as $target ) {
					$targets[ intval( $target->post_id ) ] = true;
				}
			}

			set_transient( self::TARGETS_CACHE_KEY, $targets, 10 * 60 );
		}

		return $targets;
	}

	/**
	 * Returns the _blank target status for the specified post ID.
	 *
	 * @param integer $post_id a post ID.
	 * @return bool whether it should open in a new tab.
	 */
	public static function get_target( $post_id ) {
		return (bool) self::get_post_meta( $post_id, self::TARGET_META_KEY );
	}

	/**
	 * Adds the meta box to the post or page edit screen.
	 *
	 * @param string $page the name of the current page.
	 * @param string $context the current context.
	 * @return void
	 */
	public function do_meta_boxes( $page, $context ) {
		if ( self::is_supported_post_type( $page ) && 'advanced' === $context ) {
			add_meta_box( 'page-links-to', _x( 'Page Links To', 'Meta box title', 'page-links-to' ), array( $this, 'meta_box' ), $page, 'advanced', 'low' );
		}
	}

	/**
	 * Determine whether a post type supports custom links.
	 *
	 * @param string $type The post type to check.
	 * @return bool Whether this post type supports custom links.
	 */
	public static function is_supported_post_type( $type ) {
		/*
			Plugins that use custom post types can use this filter to hide the
			PLT UI in their post type.
		*/
		$hook = 'page-links-to-post-types';

		$supported_post_types = (array) apply_filters( $hook, array_keys( get_post_types( array(
			'show_ui' => true,
		) ) ) );

		return in_array( $type, $supported_post_types );
	}

	/**
	 * Outputs the Page Links To post screen meta box.
	 *
	 * @return void
	 */
	public static function meta_box() {
		$null = null;
		$post = get_post( $null );
		echo '<p>';
		wp_nonce_field( 'cws_plt_' . $post->ID, '_cws_plt_nonce', false, true );
		echo '</p>';
		$url = self::get_link( $post->ID );
		if ( ! $url ) {
			$linked = false;
			$url = '';
		} else {
			$linked = true;
		}
	?>
		<style>
		#cws-links-to-custom-section {
			webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
			margin-left: 30px;
		}

		#cws-links-to {
			width: 75%;
		}
		</style>

		<p><?php _e( 'Point this content to:', 'page-links-to' ); ?></p>
		<p><label><input type="radio" id="cws-links-to-choose-wp" name="cws_links_to_choice" value="wp" <?php checked( ! $linked ); ?> /> <?php _e( 'Its normal WordPress URL', 'page-links-to' ); ?></label></p>
		<p><label><input type="radio" id="cws-links-to-choose-custom" name="cws_links_to_choice" value="custom" <?php checked( $linked ); ?> /> <?php _e( 'A custom URL', 'page-links-to' ); ?></label></p>
		<div id="cws-links-to-custom-section" class="<?php echo ! $linked ? 'hide-if-js' : ''; ?>">
			<p><input placeholder="http://" name="cws_links_to" type="text" id="cws-links-to" value="<?php echo esc_attr( $url ); ?>" /></p>
			<p><label for="cws-links-to-new-tab"><input type="checkbox" name="cws_links_to_new_tab" id="cws-links-to-new-tab" value="_blank" <?php checked( (bool) self::get_target( $post->ID ) ); ?>> <?php _e( 'Open this link in a new tab', 'page-links-to' ); ?></label></p>
		</div>

		<?php if ( true ) { ?>
			<style>
			#cws-links-to-survey {
				border: 1px solid #eee;
			}

			#cws-links-to-survey h3, #cws-links-to-survey p {
				margin: 1em;
			}
			</style>
			<div id="cws-links-to-survey">
				<h3>New Features Coming Soon!</h3>
				<p>Do you have a minute? <a target="_blank" href="<?php echo self::SURVEY_URL; ?>">Please take this quick survey</a> and help me decide what features to build next!</p>
			</div>
		<?php } ?>

		<script src="<?php echo self::get_url() . 'js/page-links-to.min.js?v=' . self::CSS_JS_VERSION; ?>"></script>
	<?php
	}

	/**
	 * Saves data on attachment save.
	 *
	 * @param  int $post_id The ID of the post being saved.
	 * @return int the attachment post ID that was passed in.
	 */
	function edit_attachment( $post_id ) {
		return $this->save_post( $post_id );
	}

	/**
	 * Saves data on post save.
	 *
	 * @param int $post_id a post ID.
	 * @return int the post ID that was passed in.
	 */
	public static function save_post( $post_id ) {
		if ( isset( $_REQUEST['_cws_plt_nonce'] ) && wp_verify_nonce( $_REQUEST['_cws_plt_nonce'], 'cws_plt_' . $post_id ) ) {
			if ( ( ! isset( $_POST['cws_links_to_choice'] ) || 'custom' == $_POST['cws_links_to_choice'] ) && isset( $_POST['cws_links_to'] ) && strlen( $_POST['cws_links_to'] ) > 0 && $_POST['cws_links_to'] !== 'http://' ) {
				$url = self::clean_url( stripslashes( $_POST['cws_links_to'] ) );
				self::flush_links_if( self::set_link( $post_id, $url ) );
				if ( isset( $_POST['cws_links_to_new_tab'] ) ) {
					self::flush_targets_if( self::set_link_new_tab( $post_id ) );
				} else {
					self::flush_targets_if( self::set_link_same_tab( $post_id ) );
				}
			} else {
				self::flush_links_if( self::delete_link( $post_id ) );
			}
		}

		return $post_id;
	}

	/**
	 * Cleans up a URL.
	 *
	 * @param string $url URL.
	 * @return string cleaned up URL.
	 */
	public static function clean_url( $url ) {
		$url = trim( $url );

		// Starts with 'www.'. Probably a mistake. So add 'http://'.
		if ( 0 === strpos( $url, 'www.' ) ) {
			$url = 'http://' . $url;
		}

		return $url;
	}

	/**
	 * Have a post point to a custom URL.
	 *
	 * @param int    $post_id post ID.
	 * @param string $url the URL to point the post to.
	 * @return bool whether anything changed.
	 */
	public static function set_link( $post_id, $url ) {
		return self::flush_links_if( (bool) update_post_meta( $post_id, self::LINK_META_KEY, $url ) );
	}

	/**
	 * Tell an custom URL post to open in a new tab.
	 *
	 * @param int $post_id post ID.
	 * @return bool whether anything changed.
	 */
	public static function set_link_new_tab( $post_id ) {
		return self::flush_targets_if( (bool) update_post_meta( $post_id, self::TARGET_META_KEY, '_blank' ) );
	}

	/**
	 * Tell an custom URL post to open in the same tab.
	 *
	 * @param int $post_id post ID.
	 * @return bool whether anything changed.
	 */
	public static function set_link_same_tab( $post_id ) {
		return self::flush_targets_if( delete_post_meta( $post_id, self::TARGET_META_KEY ) );
	}

	/**
	 * Discard a custom URL and point a post to its normal URL.
	 *
	 * @param int $post_id post ID.
	 * @return bool whether the link was deleted.
	 */
	public static function delete_link( $post_id ) {
		$return = self::flush_links_if( delete_post_meta( $post_id, self::LINK_META_KEY ) );
		self::flush_targets_if( delete_post_meta( $post_id, self::TARGET_META_KEY ) );

		// Old, unused data that we can delete on the fly.
		delete_post_meta( $post_id, '_links_to_type' );

		return $return;
	}

	/**
	 * Flushes the links transient cache if the condition is true.
	 *
	 * @param bool $condition whether to proceed with the flush.
	 * @return bool whether the flush happened.
	 */
	public static function flush_links_if( $condition ) {
		if ( $condition ) {
			self::flush_links_cache();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Flushes the targets transient cache if the condition is true.
	 *
	 * @param bool $condition whether to proceed with the flush.
	 * @return bool whether the flush happened.
	 */
	public static function flush_targets_if( $condition ) {
		if ( $condition ) {
			self::flush_targets_cache();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Flushes the links transient cache.
	 *
	 * @return bool whether the flush attempt occurred.
	 */
	public static function flush_links_cache() {
		return delete_transient( self::LINKS_CACHE_KEY );
	}

	/**
	 * Flushes the targets transient cache.
	 *
	 * @return bool whether the flush attempt occurred.
	 */
	public static function flush_targets_cache() {
		return delete_transient( self::TARGETS_CACHE_KEY );
	}

	/**
	 * Filter for post links.
	 *
	 * @param string      $link the URL for the post or page.
	 * @param int|WP_Post $post post ID or object.
	 * @return string output URL.
	 */
	public function link( $link, $post ) {
		if ( $this->replace ) {
			$post = get_post( $post );

			$meta_link = self::get_link( $post->ID );

			if ( $meta_link ) {
				$link = esc_url( $meta_link );
				if ( ! is_admin() && self::get_target( $post->ID ) ) {
					$link .= '#new_tab';
				}
			}
		}

		return $link;
	}

	/**
	 * Returns the original URL of the post.
	 *
	 * @param null|int|WP_Post $post The post to fetch.
	 * @return string The post's original URL.
	 */
	function original_link( $post = null ) {
		$this->replace = false;
		$url = get_permalink( $post );
		$this->replace = true;

		return $url;
	}

	/**
	 * Performs a redirect.
	 *
	 * @return void
	 */
	function template_redirect() {
		$link = self::get_redirect();

		if ( $link ) {
			wp_redirect( $link, 301 );
			exit;
		}
	}

	/**
	 * Gets the redirection URL.
	 *
	 * @return string|bool the redirection URL, or false.
	 */
	public static function get_redirect() {
		if ( ! is_singular() || ! get_queried_object_id() ) {
			return false;
		}

		$link = self::get_link( get_queried_object_id() );

		// Convert server- and protocol-relative URLs to absolute URLs.
		if ( '/' === $link[0] ) {
			// Protocol-relative.
			if ( '/' === $link[1] ) {
				$link = set_url_scheme( 'http:' . $link );
			} else {
				// Host-relative.
				$link = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $link );
			}
		}

		if ( 'mailto' !== parse_url( $link, PHP_URL_SCHEME ) ) {
			$link = str_replace( '@', '%40', $link );
		}

		return $link;
	}

	/**
	 * Filters the list of pages to alter the links and targets.
	 *
	 * @param string $pages the wp_list_pages() HTML block from WordPress.
	 * @return string the modified HTML block.
	 */
	function wp_list_pages( $pages ) {
		$highlight = false;

		// We use the "fetch all" versions here, because the pages might not be queried here.
		$links = self::get_links();
		$targets = self::get_targets();
		$targets_by_url = array();

		foreach ( array_keys( $targets ) as $targeted_id ) {
			$targets_by_url[ $links[ $targeted_id ] ] = true;
		}

		if ( ! $links ) {
			return $pages;
		}

		$this_url = ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		foreach ( (array) $links as $id => $page ) {
			if ( isset( $targets_by_url[ $page ] ) ) {
				$page .= '#new_tab';
			}

			if ( str_replace( 'http://www.', 'http://', $this_url ) === str_replace( 'http://www.', 'http://', $page ) || ( is_home() && str_replace( 'http://www.', 'http://', trailingslashit( get_bloginfo( 'url' ) ) ) === str_replace( 'http://www.', 'http://', trailingslashit( $page ) ) ) ) {
				$highlight = true;
				$current_page = esc_url( $page );
			}
		}

		if ( count( $targets_by_url ) ) {
			foreach ( array_keys( $targets_by_url ) as $p ) {
				$p = esc_url( $p . '#new_tab' );
				$pages = str_replace( '<a href="' . $p . '"', '<a href="' . $p . '" target="_blank"', $pages );
			}
		}

		if ( $highlight ) {
			$pages = preg_replace( '| class="([^"]+)current_page_item"|', ' class="$1"', $pages ); // Kill default highlighting.
			$pages = preg_replace( '|<li class="([^"]+)"><a href="' . preg_quote( $current_page ) . '"|', '<li class="$1 current_page_item"><a href="' . $current_page . '"', $pages );
		}

		return $pages;
	}

	/**
	 * Filters nav menu objects and adds target=_blank to the ones that need it.
	 *
	 * @param  array $items nav menu items.
	 * @return array modified nav menu items.
	 */
	public static function wp_nav_menu_objects( $items ) {
		$new_items = array();

		foreach ( $items as $item ) {
			if ( isset( $item->object_id ) && self::get_target( $item->object_id ) ) {
				$item->target = '_blank';
			}

			$new_items[] = $item;
		}

		return $new_items;
	}

	/**
	 * Hooks in as a post is being loaded for editing and conditionally adds a notice.
	 *
	 * @return void
	 */
	public function load_post() {
		if ( isset( $_GET['post'] ) && self::get_link( (int) $_GET['post'] ) ) {
			$this->hook( 'admin_notices', 'notify_of_external_link' );
		}
	}

	public static function ajax_dismiss_notice() {
		if ( isset( $_GET['plt_notice'] ) ) {
			self::dismiss_notice( $_GET['plt_notice'] );
		}
	}

	/**
	 * Whether a message should be displayed.
	 *
	 * @return bool Whether to display the message.
	 */
	public static function should_display_message() {
		$start_time = 1529283010;
		$end_time = $start_time + WEEK_IN_SECONDS;

		return time() > $start_time && time() < $end_time && ! self::has_dismissed_notice( self::MESSAGE_ID ) && current_user_can( 'manage_options' );
	}

	/**
	 * Return the notices which have been dismissed.
	 *
	 * @return array The list of notice IDs that have been dismissed.
	 */
	public function get_dismissed_notices() {
		return get_option( self::DISMISSED_NOTICES, array() );
	}

	/**
	 * Mark a notice as dismissed.
	 *
	 * @param int $id The notice ID to dismiss.
	 * @return void
	 */
	public static function dismiss_notice( $id ) {
		$notices = self::get_dismissed_notices();
		$notices[] = (int) $id;

		$notices = array_unique( $notices );
		update_option( self::DISMISSED_NOTICES, $notices );
	}

	/**
	 * Whether anyone on this site has dismissed the given notice.
	 *
	 * @param int $id The ID of the notice.
	 * @return bool Whether anyone has dismissed it.
	 */
	public static function has_dismissed_notice( $id ) {
		$dismissed_notices = get_option( self::DISMISSED_NOTICES, array() );

		return in_array( (int) $id, $dismissed_notices );
	}

	/**
	 * Output the generic notice.
	 *
	 * @return void
	 */
	public static function notify_generic() {
		?>
		<div id="page-links-to-notification" class="notice updated is-dismissible"><?php _e( '<h3>Page Links To</h3><p>Do you have a minute? <a target="_blank" href="' . self::SURVEY_URL . '" class="plt-dismiss">Please take this quick survey</a> and help me decide what features to build next!</p><p><a class="button plt-dismiss" target="_blank" href="' . self::SURVEY_URL . '">Take the survey</a>&nbsp;&nbsp;<small><a href="#" class="plt-dismiss">No thanks</a></small></p>', 'page-links-to' ); ?></div>
		<script>
			(function($){
				var $plt = $('#page-links-to-notification');
				$plt
					.on('click', '.notice-dismiss', function(e){
						$.ajax( ajaxurl, {
							type: 'GET',
							data: {
								action: 'plt_dismiss_notice',
								plt_notice: <?php echo json_encode( self::MESSAGE_ID ); ?>
							}
						});
					})
					.on('click', '.plt-dismiss', function(e){
						$(this).parents('.notice').first().find('.notice-dismiss').click();
					});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Outputs a notice that the current post item is pointed to a custom URL.
	 *
	 * @return void
	 */
	public static function notify_of_external_link() {
		?>
		<div class="notice updated"><p><?php _e( '<strong>Note</strong>: This content is pointing to a custom URL. Use the &#8220;Page Links To&#8221; box to change this behavior.', 'page-links-to' ); ?></p></div>
		<?php
	}

	/**
	 * Adds a GitHub link to the plugin meta.
	 *
	 * @param array  $links the current array of links.
	 * @param string $file the current plugin being processed.
	 * @return array the modified array of links.
	 */
	public static function plugin_row_meta( $links, $file ) {
		if ( $file === plugin_basename( self::FILE ) ) {
			return array_merge(
				$links,
				array( '<a href="https://github.com/markjaquith/page-links-to" target="_blank">GitHub</a>' )
			);
		} else {
			return $links;
		}
	}

	/**
	 * Returns the URL of this plugin's directory.
	 *
	 * @return string this plugin's directory URL.
	 */
	public static function get_url() {
		return plugin_dir_url( self::FILE );
	}

	/**
	 * Returns the filesystem path of this plugin's directory.
	 *
	 * @return string this plugin's directory filesystem path.
	 */
	public static function get_path() {
		return plugin_dir_path( self::FILE );
	}
}

// Bootstrap everything.
new CWS_PageLinksTo;

/**
 * Returns the original URL of the post.
 *
 * @param null|int|WP_Post $post The post to fetch.
 * @return string The post's original URL.
 */
function plt_get_original_permalink( $post = null ) {
	return CWS_PageLinksTo::get_instance()->original_link( $post );
}
