=== Plugin Name ===
Contributors: markjaquith
Donate link: http://txfx.net/wordpress-plugins/donate
Tags: page, redirect, link, external link, repoint
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: trunk

Lets you make a WordPress page or post link to a URL of your choosing, instead of its WordPress post or page.

== Description ==

This plugin allows you to make a WordPress page or post link to a URL of your choosing, instead of its WordPress page or post URL. It also will redirect people who go to the old (or "normal") URL to the new one you've chosen (`301 Moved Permanently` redirects are standard, but you can choose a `302 Moved Temporarily` redirect if you wish).

This functionality is useful for setting up navigational links to non-WordPress sections of your site or to off-site resources.

You can also use it to create a hand-crafted menu that links to pages, posts, categories, or anything within your site.

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

== Frequently Asked Questions ==

= How do I make it so that a page doesn't link to anything? I'd like to use it as a dummy container. =

Just use "#" at the link. That won't go anywhere.

= Can this be used to repoint categories to an arbitrary URL? =

Not at this time. I'm considering it as a future feature.

= My links are sending me to http://myblog.com/www.site-i-wanted-to-link-to.com ... why? =

If you want to link to a full URL, you *must* include the `http://` portion.

= Can I link to relative URLs? =

Yes. Linking to `/my-photos.php` is a good idea, as it'll still work if you move your site to a different domain.

== Changelog ==

= 2.4 =
* Rewrote using Singleton best practices
* Fixed a regex bug that could break current menu highlighting. props skarab

= 2.3 =
* Fixed a bug with current menu item highlighting

= 2.2 =
* Cleanup
* compatibility tweaks to interoperate with a few other plugins
* prompt http:// and auto-add it if a URL starts with "www."

= 2.1 =
* WordPress MU compatibility for when `switch_to_blog()` is used... it now uses `$blog_id` to keep their caches from stomping on each other

= 2.0 =
* Allow one-character URLs so that things like "#" (dummy link) are possible

= 1.9 =
* Fixed "open in new window" functionality