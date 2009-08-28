=== Plugin Name ===
Contributors: markjaquith
Donate link: http://txfx.net/code/wordpress/
Tags: page, redirect, link, external link, repoint
Requires at least: 2.7
Tested up to: 2.8.4
Stable tag: trunk

Page Links To allows you to make a WordPress page or post link to a URL of your choosing, instead of its WordPress post or page.

== Description ==

Page Links To is a plugin that allows you to make a WordPress page or post link to a URL of your choosing, instead of its WordPress page or post URL. It also will redirect people who go to the old (or "normal") URL to the new one, using a redirect style of your choosing (`301 Moved Permanently` is standard, but you can enable `302 Moved Temporarily` redirects if you wish.)

This is useful for setting up navigational links to non-WordPress sections of your site or to off-site resources.

== Installation ==

1. Upload the `page-links-to` folder to your `/wp-content/plugins/` directory

2. Activate the "Page Links To" plugin in your WordPress administration interface

3. Create (or edit) a page or a post to have a title of your choosing (leave the content blank)

4. Down below, in the advanced section, find the Page Links To widget and add a URL of your choosing

5. Optionally check the boxes to enable link opening in a new browser window, or `302 Moved Temporarily` redirects

6. Save the post or page

7. Done! Now that post or page will point to the URL that you chose

== Screenshots ==

1. The Page Links To meta box in action

== Changelog ==

= 2.1 =
* WordPress MU compatibility for when switch_to_blog() is used... it now uses $blog_id to keep their caches from stomping on each other

= 2.0 =
* Allow one-character URLs so that things like "#" (dummy link) are possible

= 1.9 =
* Fixed "open in new window" functionality