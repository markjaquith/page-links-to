(function($){
	$('input[type=radio]', '#page-links-to').change(function(){
		if ( 'wp' == $(this).val() ) {
			$('#txfx-links-to-alternate-section').fadeOut();
		} else {
			$('#txfx-links-to-alternate-section').fadeIn(function(){
				i = $('#txfx-links-to');
				i.focus().val(i.val());
			});
		}
	});
})(jQuery);