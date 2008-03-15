=== Plugin Name ===
Contributors: markjaquith
Donate link: http://txfx.net/code/wordpress/
Tags: 
Requires at least: 1.5.1.3
Tested up to: 2.3.3
Stable tag: trunk

Page Links To allows you to make certain WordPress pages or posts link to a URL of your choosing, instead of their WordPress post or page.

== Description ==

Page Links To is a plugin that allows you to make certain WordPress pages or posts link to a URL of your choosing, instead of their WordPress page or post URL. It also will redirect people who go to the old (or "normal") URL to the new one, using a redirect style of your choosing (`302 Moved Temporarily` is standard, but you can enable `301 Moved Permanently` redirects if you wish.)

== Installation ==

1. Upload `page-links-to.php` to your `/wp-content/plugins/` directory

2. Activate the "Page Links To" plugin in your WordPress administration interface

3. Create (or edit) a page to have a title of your choosing, and a parent page of your choosing (leave the content blank)

4. Down below, add a meta key of `links_to` and give a full URL as its value

5. Done! Now that post/page will point to the URL you chose.

6. Optionally, you can create a `links_to_target` meta key, and provide the target you would like for the link (like `_new`, to open the link in a new window).