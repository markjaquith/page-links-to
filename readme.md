# Page Links To #

[![Build Status](https://travis-ci.org/markjaquith/page-links-to.svg?branch=master)](https://travis-ci.org/markjaquith/page-links-to)  

Contributors: markjaquith  
Donate link: http://txfx.net/wordpress-plugins/donate  
Tags: page, redirect, link, external link, repoint  
Requires at least: 4.8  
Tested up to: 4.9.4  
Stable tag: 2.9.10  

Lets you make a WordPress page (or other content type) link to an external URL of your choosing, instead of its WordPress URL.

## Description ##

This plugin allows you to make a WordPress page or post link to a URL of your choosing, instead of its WordPress page or post URL. It also will redirect people who go to the old (or "normal") URL to the new one you've chosen.

This functionality is useful for setting up navigational links to non-WordPress sections of your site or to off-site resources.

You can also use it to create a hand-crafted menu that links to pages, posts, categories, or anything within your site.

## Installation ##

1. Upload the `page-links-to` folder to your `/wp-content/plugins/` directory.

2. Activate the "Page Links To" plugin in your WordPress administration interface.

3. Create (or edit) a page or a post to have a title of your choosing (leave the content blank).

4. Down below, in the advanced section, find the Page Links To widget, select "An alternate URL", and add a URL of your choosing.

5. Optionally check the box to enable link opening in a new browser window.

6. Save the post or page.

7. Done! Now that post or page will point to the URL that you chose.

## Screenshots ##

1. The Page Links To meta box in action

## Frequently Asked Questions ##

### How do I make it so that a page doesn't link to anything? I'd like to use it as a dummy container. ###

Just use "#" as the link. That won't go anywhere.

### Can this be used to repoint categories to an arbitrary URL? ###

Not currently. Please contact me if you're interested in that functionality.

### My links are sending me to http://myblog.com/site-i-wanted-to-link-to.com ... why? ###

If you want to link to a full URL, you *must* include the `http://` portion.

### Can I link to relative URLs? ###

Yes. Linking to `/my-photos.php` is a good idea, as it'll still work if you move your site to a different domain.

## Contribute ##

You can contribute (or report bugs) on [Github](https://github.com/markjaquith/page-links-to/).

## Changelog ##

### 2.9.10 ###
* Bump supported version

### 2.9.9 ###
* Back out jQuery protection code that causes issues on some sites

### 2.9.8 ###
* Added a Russian translation
* Maintain a reference to WordPress' jQuery version
* Modernize build tools

### 2.9.6 ###
* Fixed an issue with redirects that have `@` in the URL
* Fixed issues with setting and displaying custom URLs for attachments

### 2.9.5 ###
* Made relative URLs absolute in redirects
* Fixed a potential PHP warning
* Registered the metadata fields for better XML-RPC integration

### 2.9.4 ###
* Add Hungarian translation.

### 2.9.3 ###
* Only rely on an internal cache for `wp_list_pages()` processing, and time-limit the cache.
* Work around some weird edge cases

### 2.9.2 ###
* Restore WordPress 3.4.x functionality.

### 2.9.1 ###
* Fix a redirection bug in 2.9

### 2.9 ###
* Respect "open in new tab" setting in more custom situations, like custom loops and widgets.
* Add unit tests
* Massive code refactoring
* Added translations for: Spanish, Catalan, French.

### 2.8 ###
* Added translations for: Swedish, Hebrew.

### 2.7.1 ###
* Fix an array bug

### 2.7 ###
* Fix a PHP notice
* Use JS to open links in an external window, even outside of nav menus
* Completely revamped UI
* Several translations

### 2.6 ###
* Proper linking for custom post types (insead of just a 301).
* Fixed a bug that prevented links from opening in a new window.
* Notifies people when they are editing content that uses this plugin.
* Removed the option to set redirection type. Always 301, now.
* Removed some PHP4 and WP 2.8 back compat stuff.

### 2.5 ###
* Allow all show_ui post types to use the meta box.
* Introduce a filter so a plugin can remove a post type from the list.
* Target filtering for WordPress nav menus.
* Silence some PHP notices. Props Ross McKay, Bill Erickson.

### 2.4.1 ###
* Fixed typo that was preventing 302 redirects from working. props Ryan Murphy.
* Fixed a random PHP notice

### 2.4 ###
* Rewrote using Singleton best practices
* Fixed a regex bug that could break current menu highlighting. props skarab

### 2.3 ###
* Fixed a bug with current menu item highlighting

### 2.2 ###
* Cleanup
* compatibility tweaks to interoperate with a few other plugins
* prompt http:// and auto-add it if a URL starts with "www."

### 2.1 ###
* WordPress MU compatibility for when `switch_to_blog()` is used... it now uses `$blog_id` to keep their caches from stomping on each other

### 2.0 ###
* Allow one-character URLs so that things like "#" (dummy link) are possible

### 1.9 ###
* Fixed "open in new window" functionality
