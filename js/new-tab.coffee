jQuery ($) ->
	$( 'body' ).on 'click', 'a', (e) ->
		i = $ this
		i.attr( 'target', '_blank' ) if $.inArray( i.attr('href'), pltNewTabURLs ) > -1