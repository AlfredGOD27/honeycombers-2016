(function($) {

	$('.favorites-nav .view-all .btn').on( 'click', function() {
		$(this).closest('.favorites-nav').addClass('show-all');
	});

	$('body').on( 'click', '.add-to-folder', function(e) {
		e.preventDefault();

		var self = $(this);

		if( self.hasClass('added') )
			return;

		$.ajax({
			url: ajax_object.ajaxurl,
			type: 'POST',
			data: {
				action: 'hc_ajax_add_item_to_folder',
				folder_id: self.data('folder_id'),
				item_id: self.data('item_id')
			},
			success: function( json ) {

				var result = JSON.parse(json);

				if( 'success' === result.status ) {
					self.closest('li').addClass('added');
				} else {
					alert(result.message);
				}
			}
		});
	});

})( window.jQuery );
