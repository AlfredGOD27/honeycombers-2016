(function($) {

	if( !$('body').hasClass('infinite-scroll') )
		return;

	var update_urls = !!(window.history && window.history.pushState),
		more_html = '<button type="button" class="il-load-more"><span><i class="ico-arrow-down"></i><label>more stories</label></span></button>',
		loading_html = '<div class="il-loading">' + hc_spinner_html + '</div>';

	function queue_next_page() {

		$('.content').imagesLoaded( function() {
			$('.content').append( more_html );

			$('.content').waypoint(
				function( direction ) {
					load_next_page();
					this.destroy();
				},
				{
					offset: 'bottom-in-view'
				}
			);
		});

	}

	function load_next_page() {

		var href = $('.pagination .pagination-next a').attr('href');

		$('body').addClass('il-loading-content');

		$('.content .il-load-more').remove();
		$('.content').append( loading_html );

		$.ajax({
			url: href,
			dataType: 'html',
			success: function( data ) {
				var html = $(data);

				$('.pagination').remove();
				$('.content .il-loading').remove();
				$('.content').append( html.find('.content').html() );

				$('body').removeClass('il-loading-content');

				if( 'function' === typeof ga ) {
					path = href.replace('https://', '');
					path = href.replace('http://', '');
					path = path.split('/');

					if( path.length > 1 ) {
						path.shift();
						path = path.join('/');
						ga( 'send', 'pageview', '/' + path );
					}
				}

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

	$('.content').on( 'click', '.il-load-more', load_next_page );

	queue_next_page();

})( window.jQuery );
