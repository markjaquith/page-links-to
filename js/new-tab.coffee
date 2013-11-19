# Grab the right copy of jQuery. Plugins and themes that
# enqueue a custom version of jQuery can go to hell.
do (window) ->
	jQ = window.jQueryWP or window.jQuery
	jQ ($) ->
		if ( typeof $.fn.on is 'function' )
			$( 'body' ).on 'click', 'a', (e) ->
				i = $ this
				i.attr( 'target', '_blank' ) if $.inArray( i.attr('href'), pltNewTabURLs ) > -1
		else
			# You know what? If you're loading a copy of jQuery that doesn't
			# support `.on()`, FUCK YOU and the donkey you rode in on.
			console?.log 'Page Links To: Some other code has overridden the WordPress copy of jQuery. This is bad. Because of this, Page Links To cannot open links in a new window.'