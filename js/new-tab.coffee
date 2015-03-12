# Grab the right copy of jQuery. Plugins and themes that
# enqueue a custom version of jQuery can go to hell.
do (window) ->
	jQ = window.jQueryWP or window.jQuery
	jQ ($) ->
		newTab = ($el) ->
			$el.attr('href', $el.attr('href').replace(/#new_tab/, '')).attr('target', '_blank')

		newTabs = $ 'a[href$="#new_tab"]'
		newTabs.each (i, el) ->
			$el = $ el
			newTab $el

		if ( typeof $.fn.on is 'function' )
			$( 'body' ).on 'click', 'a', (e) ->
				$el = $ this
				newTab $el if -1 isnt $el.attr('href').indexOf('#new_tab')
		else
			# You know what? If you're loading a copy of jQuery that doesn't
			# support `.on()`, FUCK YOU and the donkey you rode in on.
			console?.log 'Page Links To: Some other code has overridden the WordPress copy of jQuery. This is bad. Because of this, Page Links To cannot open links in a new window.'