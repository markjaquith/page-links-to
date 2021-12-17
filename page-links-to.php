<?php
/**
 * The Page Links To plugin class.
 *
 * @package PageLinks
 *
 * Plugin Name: Page Links To
 * Plugin URI: http://txfx.net/wordpress-plugins/page-links-to/
 * Description: Allows you to point WordPress pages or posts to a URL of your choosing.  Good for setting up navigational links to non-WP sections of your site or to off-site resources.
 * Version: 3.3.6
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

// Main plugin class.
require( dirname( __FILE__ ) . '/classes/plugin.php' );

// Functions.
require( dirname( __FILE__ ) . '/inc/functions.php' );

// Bootstrap everything.
new CWS_PageLinksTo( __FILE__ );
