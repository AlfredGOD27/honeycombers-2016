(function($) {

	if( !$('body').hasClass('infinite-scroll') )
		return;

	var update_urls = !!(window.history && window.history.pushState);

	function queue_next_page() {

		$('.content').waypoint(
			function( direction ) {
				load_next_page();
				this.destroy();
			},
			{
				offset: 'bottom-in-view'
			}
		);

	}

	function load_next_page() {

		var href = $('.pagination .pagination-next a').attr('href');

		$('body').addClass('loading-content');

		$.ajax({
			url: href,
			dataType: 'html',
			success: function( data ) {
				var html = $(data);

				$('.pagination').remove();
				$('.content').append( html.find('.content').html() );

				$('body').removeClass('loading-content');

				if( update_urls )
					history.pushState( {}, html.find('title').text(), href );

				if( html.find('.pagination .pagination-next a').length > 0 ) {
					queue_next_page();
				} else {
					$('body').removeClass('loaded-all-content');
				}
			}
		});

	}

	queue_next_page();

})( window.jQuery );
