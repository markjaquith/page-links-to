<?php
/**
 * The Page Links To plugin.
 *
 * @package PageLinksTo
 */

defined( 'WPINC' ) or die;

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

	/**
	 * The main plugin file path.
	 *
	 * @var string
	 */
	private $file;

	const LINK_META_KEY = '_links_to';
	const TARGET_META_KEY = '_links_to_target';
	const VERSION_KEY = 'txfx_plt_schema_version';
	const DISMISSED_NOTICES = 'page_links_dismissed_options';
	const MESSAGE_ID = 4;
	const NEWSLETTER_URL = 'https://pages.convertkit.com/8eb23c1339/1ce4614706';
	const CSS_JS_VERSION = '3.3.6';

	/**
	 * Whether to replace WP links with their specified URLs.
	 *
	 * @var bool
	 */
	protected $replace = true;

	/**
	 * Class constructor. Adds init hook.
	 *
	 * @param string $file The main plugin file path.
	 */
	function __construct( $file ) {
		self::$instance = $this;
		self::$instance->file = $file;
		$this->hook( 'init' );
	}

	/**
	 * Get the plugin instance.
	 *
	 * @return CWS_PageLinksTo The plugin class instance.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Returns the main plugin file path.
	 *
	 * @return string The main plugin file path.
	 */
	public function get_file() {
		return $this->file;
	}

	/**
	 * Add a WordPress hook (action/filter).
	 *
	 * @param mixed $hook first parameter is the name of the hook. If second or third parameters are included, they will be used as a priority (if an integer) or as a class method callback name (if a string).
	 * @return true Will always return true.
	 */
	public function hook( $hook ) {
		$args = func_get_args();
		$priority = 10;
		$method = self::sanitize_method( $hook );
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
	 * Includes a file (relative to the plugin base path)
	 * and optionally globalizes a named array passed in.
	 *
	 * @param string $file The file to include.
	 * @param array  $data A named array of data to globalize.
	 * @return void
	 */
	public function include_file( $file, $data = array() ) {
		extract( $data, EXTR_SKIP );
		include( $this->get_path() . $file );
	}

	/**
	 * Bootstraps the upgrade process and registers all the hooks.
	 *
	 * @return void
	 */
	public function init() {
		// Check to see if any of our data needs to be upgraded.
		$this->maybe_upgrade();

		// Load translation files.
		load_plugin_textdomain( 'page-links-to', false, basename( dirname( $this->file ) ) . '/languages' );

		// Init hook.
		do_action( 'page_links_to_init', $this );

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
		$this->hook( 'page_link', 'link', 20 );
		$this->hook( 'post_link', 'link', 20 );
		$this->hook( 'post_type_link', 'link', 20 );
		$this->hook( 'attachment_link', 'link', 20 );

		// Non-standard priority hooks.
		$this->hook( 'do_meta_boxes', 20 );
		$this->hook( 'admin_bar_menu', 999 );
		$this->hook( 'wp_ajax_sample-permalink', 'disable_replacements', -999 );

		// Non-standard callback hooks.
		$this->hook( 'load-post.php', 'load_post' );
		$this->hook( 'wp_ajax_plt_dismiss_notice', 'ajax_dismiss_notice' );
		$this->hook( 'wp_ajax_plt_quick_add', 'ajax_quick_add' );

		// Standard hooks.
		$this->hook( 'wp_list_pages' );
		$this->hook( 'template_redirect' );
		$this->hook( 'save_post' );
		$this->hook( 'wp_enqueue_scripts' );
		$this->hook( 'edit_attachment' );
		$this->hook( 'wp_nav_menu_objects' );
		$this->hook( 'plugin_row_meta' );
		$this->hook( 'display_post_states' );
		$this->hook( 'admin_footer' );
		$this->hook( 'admin_enqueue_scripts' );
		$this->hook( 'enqueue_block_editor_assets' );
		$this->hook( 'admin_menu' );

		$this->hook( 'rest_api_init' );

		// Gutenberg.
		$this->hook( 'use_block_editor_for_post', 99999 );

		// Page row actions.
		$this->hook( 'page_row_actions' );
		$this->hook( 'post_row_actions', 'page_row_actions' );

		$this->hook( 'init', 'register_meta_keys', 9999 );
		$this->hook( 'rest_api_init', 'register_meta_keys', 9999 );

		// Notices.
		if ( self::should_display_message() ) {
			$this->hook( 'admin_notices', 'notify_generic' );
		}
	}

	/**
	 * Checks if the specified post is going to use the block editor, and adds custom-fields support.
	 * 
	 * We have to do this because PLT requires custom-fields support to update post meta in the block editor.
	 * So if you add a custom post type without 'custom-fields' support, you'll get an error.
	 * We check that this post is going to use the block editor, and that its post type supports the block editor,
	 * and only then do we add 'custom-fields' support for the post type.
	 *
	 * @param boolean $use_block_editor Whether they are going to use the block editor for this post.
	 * @param WP_Post $post The post they are editing.
	 * @return boolean We return the original value of their decision.
	 */
	public function use_block_editor_for_post( $use_block_editor, $post ) {
		if ( $use_block_editor && self::is_supported_post_type( get_post_type( $post ) ) ) {
			add_post_type_support( get_post_type( $post ), 'custom-fields' );
		}

		return $use_block_editor;
	}

	/**
	 * Adds custom-fields support to PLT-supporting post types during REST API initialization.
	 *
	 * @return void
	 */
	function rest_api_init() {
		$post_type_names = array_keys( get_post_types() );

		foreach ( $post_type_names as $type ) {
			if ( self::is_supported_post_type( $type ) ) {
				add_post_type_support( $type, 'custom-fields' );
			}
		}
	}

	/**
	 * Registers the PLT post meta keys for supported post types.
	 *
	 * @return void
	 */
	public function register_meta_keys() {
		$post_type_names = array_keys( get_post_types() );

		foreach ( $post_type_names as $type ) {
			if ( self::is_supported_post_type( $type ) ) {
				$this->register_meta( self::LINK_META_KEY, $type );
				$this->register_meta( self::TARGET_META_KEY, $type );
				do_action( 'page_links_to_register_meta_for_post_type', $type );
			}
		}
	}

	/**
	 * Registers a post meta key for a given post type.
	 *
	 * @param string $key The key name.
	 * @param string $post_type The post type.
	 * @return boolean Whether the meta key was registered.
	 */
	public function register_meta( $key, $post_type ) {
		return register_meta(
			'post',
			$key,
			array(
				'object_subtype' => $post_type,
				'type' => 'string',
				'single' => true,
				'show_in_rest' => true,
				'auth_callback' => array( $this, 'rest_auth' ),
			)
		);
	}

	/**
	 * Determines REST API authentication.
	 *
	 * @param bool   $allowed Whether it is allowed.
	 * @param string $meta_key The meta key being checked.
	 * @param int    $post_id The post ID being checked.
	 * @param int    $user_id The user ID being checked.
	 * @param string $cap The current capability.
	 * @param array  $caps All capabilities.
	 * @return bool Whether the user can do it.
	 */
	public function rest_auth( $allowed, $meta_key, $post_id, $user_id, $cap, $caps ) {
		return user_can( $user_id, 'edit_post', $post_id );
	}

	/**
	 * Performs an upgrade for older versions
	 *
	 * * Version 3: Underscores the keys so they only show in the plugin's UI.
	 *
	 * @return void
	 */
	function maybe_upgrade() {
		// In earlier versions, the meta keys were stored without a leading underscore.
		// Since then, underscore has been codified as the standard for "something manages this" post meta.
		if ( ! get_option( self::VERSION_KEY ) || get_option( self::VERSION_KEY ) < 3 ) {
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

			// Update the database version.
			update_option( self::VERSION_KEY, 3 );

			// Only flush the cache if something changed.
			if ( $total_affected > 0 ) {
				wp_cache_flush();
			}
		}
	}

	/**
	 * Disables replacements.
	 *
	 * @return void
	 */
	public function disable_replacements() {
		$this->replace = false;
	}

	/**
	 * Enqueues frontend scripts.
	 *
	 * @return void
	 */
	public function wp_enqueue_scripts() {
		if ( self::supports( 'new_tab' ) ) {
			wp_enqueue_script( 'page-links-to', $this->get_url() . 'dist/new-tab.js', array(), self::CSS_JS_VERSION, true );
		}
	}

	/**
	 * Enqueues backend scripts.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( !is_customize_preview() && current_user_can( 'edit_posts' ) ) {
			wp_register_script( 'plt-clipboard', $this->get_url() . 'dist/clipboard.min.js', array(), self::CSS_JS_VERSION, true );
			wp_enqueue_script( 'plt-quick-add', $this->get_url() . 'dist/quick-add.js', array( 'plt-clipboard', 'jquery-ui-dialog' ), self::CSS_JS_VERSION, true );
			wp_enqueue_style( 'plt-quick-add', $this->get_url() . 'dist/quick-add.css', array( 'wp-jquery-ui-dialog' ), self::CSS_JS_VERSION );
		}
	}

	/**
	 * Enqueues block editor scripts.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		// Gutenberg.
		if ( self::is_block_editor() && self::is_supported_post_type() ) {
			wp_enqueue_script( 'plt-block-editor', $this->get_url() . 'dist/block-editor.js', array( 'wp-edit-post', 'wp-element', 'wp-plugins' ), self::CSS_JS_VERSION, true );
			wp_localize_script( 'plt-block-editor', 'pltOptions', [
				'supports' => [
					'newTab' => self::supports( 'new_tab' ),
				],
				'panelTitle' => self::get_panel_title(),
			]);
			do_action( 'page_links_to_enqueue_block_editor_assets' );
		}
	}

	/**
	 * Adds our items to the admin bar.
	 *
	 * @param WP_Admin_Bar $bar The admin bar object.
	 * @return void
	 */
	public function admin_bar_menu( $bar ) {
		if ( is_admin() ) {
			$bar->add_node( array(
				'id' => 'new-page-link',
				'title' => __( 'Page Link', 'page-links-to' ),
				'parent' => 'new-content',
				'href' => '#new-page-link',
			));
		}
	}

	/**
	 * Filters the page row actions.
	 *
	 * @param array   $actions The current array of actions.
	 * @param WP_Post $post The current post row being processed.
	 * @return array The updated array of actions.
	 */
	public function page_row_actions( $actions, $post ) {
		if ( self::get_link( $post ) ) {
			$new_actions = array();
			$inserted = false;
			$original_html = '<a href="' . esc_attr( $this->original_link( $post->ID ) ) . '" class="plt-copy-short-url" data-clipboard-text="' . esc_attr( $this->original_link( $post->ID ) ) . '" data-original-text="' . __( 'Copy Short URL', 'page-links-to' ) . '">' . __( 'Copy Short URL', 'page-links-to' ) . '</a>';
			$original_key = 'plt_original';

			foreach ( $actions as $key => $html ) {
				$new_actions[ $key ] = $html;

				if ( 'view' === $key ) {
					$inserted = true;
					$new_actions[ $original_key ] = $original_html;
				}
			}

			if ( ! $inserted ) {
				$new_actions[ $original_key ] = $original_html;
			}

			$actions = $new_actions;
		}

		return $actions;
	}

	/**
	 * Adds the Add Page Link menu item.
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_submenu_page( 'edit.php?post_type=page', '', __( 'Add Page Link', 'page-links-pro' ), 'edit_pages', 'plt-add-page-link', '__return_empty_string' );
	}

	/**
	 * Adds the quick-add HTML to the admin footer.
	 *
	 * @return void
	 */
	public function admin_footer() {
		if ( current_user_can( 'edit_pages' ) ) {
			$this->include_file( 'templates/quick-add.php' );
		}
	}

	/**
	 * Returns a single piece of post meta.
	 *
	 * @param  int    $post_id a post ID.
	 * @param  string $key a post meta key.
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
	 * Returns the link for the specified post.
	 *
	 * @param  WP_Post|int $post a post or post ID.
	 * @return mixed either a URL or false.
	 */
	public static function get_link( $post ) {
		$post = get_post( $post );
		$post_id = empty( $post ) ? null : $post->ID;

		return self::get_post_meta( $post_id, self::LINK_META_KEY );
	}

	/**
	 * Returns the _blank target status for the specified post.
	 *
	 * @param  WP_Post|int $post a post or post ID.
	 * @return bool whether it should open in a new tab.
	 */
	public static function get_target( $post ) {
		$post = get_post( $post );
		$post_id = empty( $post ) ? null : $post->ID;

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
		if ( ! self::is_block_editor() && self::is_supported_post_type( $page ) && 'advanced' === $context ) {
			add_meta_box( 'page-links-to', self::get_panel_title(), array( $this, 'meta_box' ), $page, 'advanced', 'low' );
		}
	}

	/**
	 * Determine whether a post type supports custom links.
	 *
	 * @param string $type The post type to check.
	 * @return bool Whether this post type supports custom links.
	 */
	public static function is_supported_post_type( $type = null ) {
		if ( is_null( $type ) ) {
			$type = get_post_type();
		}

		if ( is_object( $type ) ) {
			if ( isset( $type->id ) ) {
				$type = $type->id;
			}
		}

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
	public function meta_box() {
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
		<p><?php _e( 'Point this content to:', 'page-links-to' ); ?></p>
		<p><label><input type="radio" id="cws-links-to-choose-wp" name="cws_links_to_choice" value="wp" <?php checked( ! $linked ); ?> /> <?php _e( 'Its normal WordPress URL', 'page-links-to' ); ?></label></p>
		<p><label><input type="radio" id="cws-links-to-choose-custom" name="cws_links_to_choice" value="custom" <?php checked( $linked ); ?> /> <?php _e( 'A custom URL', 'page-links-to' ); ?></label></p>
		<div id="cws-links-to-custom-section" class="<?php echo ! $linked ? 'hide-if-js' : ''; ?>">
			<p><input placeholder="http://" name="cws_links_to" type="text" id="cws-links-to" value="<?php echo esc_attr( $url ); ?>" /></p>
			<?php if ( $this->supports('new_tab') ) { ?>
				<p><label for="cws-links-to-new-tab"><input type="checkbox" name="cws_links_to_new_tab" id="cws-links-to-new-tab" value="_blank" <?php checked( (bool) self::get_target( $post->ID ) ); ?>> <?php _e( 'Open this link in a new tab', 'page-links-to' ); ?></label></p>
			<?php } ?>
			<?php do_action( 'page_links_to_meta_box_bottom' ); ?>
		</div>

		<script src="<?php echo esc_url( $this->get_url() ) . 'dist/meta-box.js?v=' . self::CSS_JS_VERSION; ?>"></script>
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

	public static function supports( $feature = '' ) {
		switch( $feature ) {
			case 'new_tab':
			default:
				return apply_filters( 'page_links_to_supports_' . $feature, true );
		}
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
				self::set_link( $post_id, $url );
				if ( isset( $_POST['cws_links_to_new_tab'] ) && self::supports( 'new_tab' ) ) {
					self::set_link_new_tab( $post_id );
				} else {
					self::set_link_same_tab( $post_id );
				}
			} else {
				self::delete_link( $post_id );
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
		do_action( 'page_links_to_set_link', $post_id, $url );
		return (bool) update_post_meta( $post_id, self::LINK_META_KEY, $url );
	}

	/**
	 * Tell an custom URL post to open in a new tab.
	 *
	 * @param int $post_id post ID.
	 * @return bool whether anything changed.
	 */
	public static function set_link_new_tab( $post_id ) {
		return (bool) update_post_meta( $post_id, self::TARGET_META_KEY, '_blank' );
	}

	/**
	 * Tell an custom URL post to open in the same tab.
	 *
	 * @param int $post_id post ID.
	 * @return bool whether anything changed.
	 */
	public static function set_link_same_tab( $post_id ) {
		return delete_post_meta( $post_id, self::TARGET_META_KEY );
	}

	/**
	 * Discard a custom URL and point a post to its normal URL.
	 *
	 * @param int $post_id post ID.
	 * @return bool whether the link was deleted.
	 */
	public static function delete_link( $post_id ) {
		$return = delete_post_meta( $post_id, self::LINK_META_KEY );
		delete_post_meta( $post_id, self::TARGET_META_KEY );

		// Old, unused data that we can delete on the fly.
		delete_post_meta( $post_id, '_links_to_type' );

		do_action( 'page_links_to_delete_link', $post_id );

		return $return;
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

			if (! $post instanceof \WP_Post) {
				return $link;
			}

			$meta_link = self::get_link( $post->ID );

			if ( $meta_link ) {
				$link = apply_filters( 'page_links_to_link', $meta_link, $post, $link );
				$link = esc_url( $link );
				if ( self::supports( 'new_tab' ) && ! is_admin() && !  (defined( 'REST_REQUEST' ) && REST_REQUEST ) && self::get_target( $post->ID ) ) {
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
	 * Whether the request has a flag that should halt the redirect.
	 *
	 * @return bool
	 */
	function has_non_redirect_flag() {
		return (
			isset( $_GET['customize_theme'] ) ||
			isset( $_GET['elementor-preview'] )
		);
	}

	/**
	 * Performs a redirect.
	 *
	 * @return void
	 */
	function template_redirect() {
		$link = self::get_redirect();

		if ( $link && !self::has_non_redirect_flag() ) {
			do_action( 'page_links_to_redirect_url', get_queried_object_id(), $link );
			wp_redirect( $link, 301 );
			exit;
		}
	}

	/**
	 * Retrieves all posts that have a specified custom URL.
	 *
	 * @param string $url The URL to check.
	 * @return array Array of post objects.
	 */
	public static function get_custom_url_posts( $url ) {
		$result = new WP_Query(array(
			'post_type' => 'any',
			'meta_key' => self::LINK_META_KEY,
			'meta_value' => $url,
			'posts_per_page' => -1,
			'post_status' => 'any',
		));

		return $result->posts;
	}

	/**
	 * Retrieves all posts that have a custom URL.
	 *
	 * @return array Array of post objects.
	 */
	public static function get_all_custom_url_posts() {
		$result = new WP_Query(array(
			'post_type' => 'any',
			'meta_key' => self::LINK_META_KEY,
			'posts_per_page' => -1,
			'post_status' => 'any',
		));

		return $result->posts;
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

		if ( $link ) {
			$link = self::absolute_url( $link );
		}

		return $link;
	}

	/**
	 * Makes a relative URL into an absolute one.
	 *
	 * @param string $url The relative URL.
	 * @return string The absolute URL.
	 */
	public static function absolute_url( $url ) {
		// Convert server- and protocol-relative URLs to absolute URLs.
		if ( '/' === $url[0] ) {
			// Protocol-relative.
			if ( '/' === $url[1] ) {
				$url = set_url_scheme( 'http:' . $url );
			} else {
				// Host-relative.
				$url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $url );
			}
		}

		if ( 'mailto' !== parse_url( $url, PHP_URL_SCHEME ) ) {
			$url = str_replace( '@', '%40', $url );
		}

		return $url;
	}

	/**
	 * Filters the list of pages to alter the links and targets.
	 *
	 * @param string $output the wp_list_pages() HTML block from WordPress.
	 * @param array  $_args (Unused) the arguments passed to `wp_list_pages()`.
	 * @param array  $pages Array of WP_Post objects.
	 * @return string the modified HTML block.
	 */
	function wp_list_pages( $output, $_args = array(), $pages = array() ) {
		if ( empty( $pages ) ) {
			return $output;
		}

		$highlight = false;

		$this_url = esc_url_raw( set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) );

		foreach ( (array) $pages as $page ) {
			$page_url = self::get_link( $page->ID );

			if ( $page_url && $this_url === $page_url ) {
				$highlight = true;
				$current_page = esc_url( $page_url );
				$current_page_id = $page->ID;
			}
		}

		if ( $highlight ) {
			$output = preg_replace( '|<li class="([^"]+) current_page_item"|', '<li class="$1"', $output ); // Kill default highlighting.
			$output = preg_replace( '|<li class="(page_item page-item-' . $current_page_id . ')"|', '<li class="$1 current_page_item"', $output );
		}

		return $output;
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
			$this->hook( 'edit_form_after_title' );
			$this->hook( 'admin_notices', 'notify_of_external_link' );
			$this->replace = false;
		}
	}

	/**
	 * Ajax handler for dismissing a notice.
	 *
	 * @return void
	 */
	public static function ajax_dismiss_notice() {
		if ( isset( $_GET['plt_notice'] ) ) {
			self::dismiss_notice( $_GET['plt_notice'] );
		}
	}

	/**
	 * Ajad handler for creating a page link.
	 *
	 * @return void
	 */
	public function ajax_quick_add() {
		if ( current_user_can( 'edit_pages' ) ) {
			check_ajax_referer( 'plt-quick-add', 'plt_nonce' );

			$post = stripslashes_deep( $_POST );
			$title   = $post['plt_title'];
			$url     = $post['plt_url'];
			$slug    = $post['plt_slug'];
			$publish = (bool) $post['plt_publish'] && current_user_can( 'publish_pages' );

			$post_id = wp_insert_post(array(
				'post_type' => 'page',
				'post_status' => $publish ? 'publish' : 'draft',
				'post_title' => $title,
				'post_name' => $slug,
			));

			$this->set_link( $post_id, $url );

			$post = get_post( $post_id );

			$message = $publish ? __( 'New page link published!', 'page-links-to' ) : __( 'Page link draft saved!', 'page-links-to' );

			wp_send_json_success( array(
				'id'      => $post->ID,
				'title'   => $post->post_title,
				'wpUrl'   => $this->original_link( $post->ID ),
				'url'     => self::get_link( $post->ID ),
				'slug'    => $post->post_name,
				'status'  => $post->post_status,
				'message' => $message,
			));
		}
	}

	/**
	 * Whether a message should be displayed.
	 *
	 * @return bool Whether to display the message.
	 */
	public static function should_display_message() {
		return false;
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
		if ( self::is_block_editor() ) {
			// Nothing right now.
		} else {
			?>
			<div id="page-links-to-notification" class="notice updated is-dismissible"><h3><?php _e( 'Page Links To', 'page-links-to' ); ?></h3>
				<p><a class="button plt-dismiss" target="_blank" href="<?php echo esc_url( self::NEWSLETTER_URL ); ?>"><?php _e( 'Give Me Updates', 'page-links-to' ); ?></a>&nbsp;&nbsp;<small><a href="javascript:void(0)" class="plt-dismiss"><?php _e( 'No thanks', 'page-links-to' ); ?></a></small></p>
			</div>
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
							e.preventDefault();
							$(this).parents('.notice').first().find('.notice-dismiss').click();
						});
				})(jQuery);
			</script>
			<?php
		}
	}

	/**
	 * Whether the user is using the block editor (Gutenberg).
	 *
	 * @return bool
	 */
	public static function is_block_editor() {
		$current_screen = get_current_screen();
		return method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor();
	}

	/**
	 * Create a block editor notification.
	 *
	 * @param string $text The notification.
	 * @param string $type The type of notification.
	 * @return void
	 */
	public static function block_editor_notification( $text, $type = 'info' ) {
		if ( ! in_array( $type, array( 'error', 'warning', 'info' ) ) ) {
			return;
		}

		$type = ucfirst( $type );
		$method = "create{$type}Notice";
		?>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					if (wp.data !== undefined) {
						wp.data.dispatch('core/notices').<?php echo $method; ?>(<?php echo json_encode( $text ); ?>, {isDismissible: true, id: 'page-links-to-notice'});
					}
				});
			</script>
		<?php
	}

	/**
	 * Returns the panel title.
	 *
	 * @return string The panel title.
	 */
	public static function get_panel_title() {
		return apply_filters( 'page_links_to_panel_title', _x( 'Page Links To', 'Meta box title', 'page-links-to' ) );
	}

	/**
	 * Outputs a notice that the current post item is pointed to a custom URL.
	 *
	 * @return void
	 */
	public static function notify_of_external_link() {
		if ( self::is_block_editor() ) {
			// Disabled, currently, because these notifications can block the title, which is annoying.
			false && self::block_editor_notification( 'Note: This content is pointing to a custom URL. Use the “Page Links To” area in the sidebar to control this.', 'info' );
		} else {
			?>
				<div class="notice updated"><p><?php printf( __( '<strong>Note</strong>: This content is pointing to a custom URL. Use the &#8220;%s&#8221; box to change this behavior.', 'page-links-to' ), self::get_panel_title() ); ?></p></div>
			<?php
		}
	}

	/**
	 * Inserts an Edit link after the title.
	 *
	 * @return void
	 */
	public function edit_form_after_title() {
		$this->replace = true;
		$post = get_post();
		$link = self::get_link( $post );

		if ( ! $link ) {
			return;
		}

		echo '<div class="plt-links-to"><strong>' . __( 'Links to:', 'page-links-to' ) . '</strong> <a href="' . esc_url( $link ) . '">' . esc_html( $link ) . '</a> <button type="button" class="edit-slug button button-small hide-if-no-js">Edit</button></div>';
	}

	/**
	 * Adds a GitHub link to the plugin meta.
	 *
	 * @param array  $links the current array of links.
	 * @param string $file the current plugin being processed.
	 * @return array the modified array of links.
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $file === plugin_basename( $this->file ) ) {
			return array_merge(
				$links,
				array( '<a href="https://github.com/markjaquith/page-links-to" target="_blank">GitHub</a>' )
			);
		} else {
			return $links;
		}
	}

	/**
	 * Filter the post states to indicate which ones are linked using this plugin.
	 *
	 * @param array   $states The existing post states.
	 * @param WP_Post $post The current post object being displayed.
	 * @return array The modified post states array.
	 */
	public function display_post_states( $states, $post ) {
		$link = self::get_link( $post );

		if ( $link ) {
			$link = $this->absolute_url( $link );
			$output = '';
			$output_parts = array(
				'custom' => '<a title="' . __( 'Linked URL', 'page-links-to' ) . '" href="' . esc_url( $link ) . '" class="plt-post-state-link"><span class="dashicons dashicons-admin-links"></span><span class="url"> ' . esc_url( $link ) . '</span></a>',
			);
			$output_parts = apply_filters( 'page_links_to_post_state_parts', $output_parts, $post, $link );
			$output .= '<span class="plt-post-info">' . implode( $output_parts ) . '</span>';
			$states['plt'] = $output;
		}

		return $states;
	}

	/**
	 * Returns the URL of this plugin's directory.
	 *
	 * @return string this plugin's directory URL.
	 */
	public function get_url() {
		return plugin_dir_url( $this->file );
	}

	/**
	 * Returns the filesystem path of this plugin's directory.
	 *
	 * @return string this plugin's directory filesystem path.
	 */
	public function get_path() {
		return plugin_dir_path( $this->file );
	}
}
