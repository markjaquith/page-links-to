jQuery ($) ->
	section = $ '#txfx-links-to-custom-section'
	input = $ 'input[type=radio]', '#page-links-to'

	if input.filter('input[value="wp"]').prop 'checked'
		section.fadeTo(1, 0).hide()

	input.change ->
		if $(@).val() is 'wp'
			section.fadeTo 'fast', 0, ->
				$(@).slideUp()
		else
			section.slideDown 'fast', ->
				$(@).fadeTo 'fast', 1, ->
					i = $ '#txfx-links-to'
					i.focus().val i.val()
