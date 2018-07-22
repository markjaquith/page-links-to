<?php
defined( 'WPINC' ) or die;

wp_enqueue_script( 'jquery-ui-dialog' );
wp_enqueue_style( 'wp-jquery-ui-dialog' );
wp_enqueue_script( 'plt-quick-add' );
wp_enqueue_style( 'plt-quick-add' );
?>

<div id="plt-quick-add" class="hidden">
	<form>
		<div class="content">
			<label><?php _e( 'Title', 'page-links-pro' ); ?> <input type="text" name="title" class="regular-text" placeholder="<?php esc_attr_e( 'Page Title', 'page-links-pro' ); ?>" /></label>

			<br />

			<label><?php _e( 'URL', 'page-links-pro' ); ?> <input type="text" name="url" class="regular-text" placeholder="https://example.com/" /></label>

			<?php if ( get_option('permalink_structure') ) { ?>
				<br />
				<label><?php _e( 'Short URL', 'page-links-pro' ); ?> <code><?php echo esc_url( trailingslashit( home_url('/') ) ); ?></code><input type="text" name="slug" placeholder="<?php esc_attr_e( 'page-title', 'page-links-pro' ); ?>" /></label>
			<?php } ?>

		</div>
		<div class="footer">
			<p><span class="message"></span><?php submit_button( 'Save Draft', 'secondary', 'plt-quick-add-save', false ); ?>&nbsp;&nbsp;<?php submit_button( 'Publish', 'primary', 'plt-quick-add-publish', false ); ?></p>
		</div>
	</form>
</div>
