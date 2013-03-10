jQuery ($) ->
	$('input[type=radio]', '#page-links-to').change ->
		section = $ '#txfx-links-to-alternate-section'

		if $(@).val() is 'wp'
			section.css
				height: section.height() + 'px'
			.fadeTo 'normal', 0, ->
				$(@).slideUp()
		else
			section.slideDown 'normal', ->
				$(@).fadeTo 'normal', 1, ->
					i = $ '#txfx-links-to'
					i.focus().val i.val()
