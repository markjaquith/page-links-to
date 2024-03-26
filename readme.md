# Page Links To #

[![Build Status](https://travis-ci.org/markjaquith/page-links-to.svg?branch=master)](https://travis-ci.org/markjaquith/page-links-to)  

Contributors: markjaquith  
Donate link: https://txfx.net/wordpress-plugins/donate  
Tags: page, redirect, link, external link, repoint  
Requires at least: 4.8  
Tested up to: 6.4  
Stable tag: 3.3.7  

Lets you make a WordPress page (or port or other content type) link to a URL of your choosing (on your site, or on another site), instead of its normal WordPress URL.

## Description ##

This plugin allows you to make a WordPress page (or post or custom post type) link to a URL of your choosing, instead of its WordPress URL. It also will redirect people who go to the old (or "normal") URL to the new one you've chosen.

**Common uses:**

* Set up navigational links to non-WordPress sections of your site or to off-site resources.
* Publish content on other blogs (or other services, like Medium) but have them show up in your WordPress posts stream. All you have to supply is a title and a URL. The post title will link to the content on the other site.
* For store operators, you can link to products on other retailer's sites (maybe with an affiliate code) but have them show up like they're products in your store.
* Create a "pretty URL" for something complicated. Say you have https://example.com/crazy-store-url.cgi?search=productId&sourceJunk=cruft ... just create a WordPress page called "My Store" and use Page Links To to point it to the ugly URL. Give people the new URL: https://example.com/my-store/ and it will redirect them!

## Installation ##

1. Upload the `page-links-to` folder to your `/wp-content/plugins/` directory.

2. Activate the "Page Links To" plugin.

**Existing Content Usage:**

1. Edit a page (or post or custom post type).

2. Below, find the Page Links To widget, select "A custom URL", and add a URL of your choosing.

3. Optionally check the box to enable link opening in a new browser tab.

4. Save the page (or post or custom post type).

5. Done! Now that content will point to the URL that you chose. Also, if anyone had the old WordPress URL for that content, they will be redirected to the custom URL if they visit.

**Creating New Page Links:**

1. Click Pages > Add New Page Link.

2. Provide a title and a destination URL.

3. Optionally provide a custom slug, which will be used in creating a local redirect URL.

4. Click Publish.

## Screenshots ##

1. The Page Links To meta box in action
2. The quick Add Page Link dialog.

## Frequently Asked Questions ##

### How do I make it so that a page doesn't link to anything? I'd like to use it as a dummy container. ###

Just use "#" as the link. That won't go anywhere.

### Can this be used to repoint categories to an arbitrary URL? ###

Not currently. Please contact me if you're interested in that functionality.

### My links are sending me to http://myblog.com/site-i-wanted-to-link-to.com ... why? ###

If you want to link to a full URL, you *must* include the `http://` portion.

### Can I link to relative URLs for URLs on the same domain? ###

Yes. Linking to `/my-photos.php` is a good idea, as it'll still work if you move your site to a different domain.

## Contribute ##

You can contribute (or report bugs) on [Github](https://github.com/markjaquith/page-links-to/).

## Changelog ##

### 3.3.5 ###
* Fix a bug that could cause new installs to constantly try to update the storage format.

### 3.3.4 ###
* Bug fixes

### 3.3.3 ###
* Add a SlotFill in the Block Editor, for extension.
* Fix New Tab support in Internet Explorer.

### 3.3.2 ###
* Fix a small new tab JS error.

### 3.3.1 ###
* Fix WordPress 5.2 Block Editor (plugin will NOT be in its own panel if you're using WordPress 5.2).

### 3.3.0 ###
* Move Block Editor UI into its own panel.
* Compatibility with Elementor.
* Allow posts to load in the customizer (used by some front-end editing plugins).
* Allow the "open in new tab" functionality to be completely disabled with a filter.
* Make "open in new tab" more reliable.

### 3.2.2 ###
* Bug fixes
* Better compat with custom post types in the Block Editor

### 3.2.1 ###
* Bug fixes

### 3.2.0 ###
* Block Editor improvements
* Smaller build

### 3.1.2 ###
* Customizer bug fix

### 3.1.1 ###
* Block Editor bugfixes

### 3.1.0 ###
* Support for the Block Editor (Gutenberg)

### 3.0.1 ###
* Fixed a PHP warning caused by some themes

### 3.0.0 ###
* Quick page link adding UI
* External link indicator
* Short URL copying
* Short URL display on edit screen

### 2.11.2 ###
* Newsletter

### 2.11.1 ###
* Restore PHP 5.3 compatibility, broken in 2.11.0

### 2.11.0 ###
* Code cleanup

### 2.10.4 ###
* New screenshot and assets

### 2.10.3 ###
* Fix readme.txt

### 2.10.2 ###
* Fix bug in Internet Explorer

### 2.10.1 ###
* Version bump

### 2.10.0 ###
* Switch to ES6 and Babel from CoffeeScript
* Remove jQuery as front-end requirement
* Bump supported version

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
