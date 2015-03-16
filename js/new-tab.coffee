# Grab the right copy of jQuery. Plugins and themes that
# enqueue a custom version of jQuery can go to hell.
do (window) ->
	$ = window.jQueryWP or window.jQuery

	# Makes an anchor element open in a new tab
	newTab = (el) ->
		$el = $ el
		$el
			.attr 'href', $el.attr('href').replace /#new_tab/, ''
			.attr 'target', '_blank'

	# Attach click handler immediately
	if typeof $.fn.on is 'function'
		$ document
			.on 'click', 'a[href$="#new_tab"]', (e) ->
				newTab @
	else
		console?.log 'Page Links To: Some other code
			has overridden the WordPress copy of jQuery. This
			is bad. Because of this, Page Links To cannot open
			links in a new window.'

	# On document ready, transform all new tab anchors
	$ ->
		$ 'a[href$="#new_tab"]'
			.each (i, el) ->
				newTab el
