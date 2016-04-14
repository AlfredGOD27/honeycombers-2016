(function($) {

	if( !$('body').hasClass('page-template-page_home') )
		return;

	if( !im.lessThan('tablet') ) {
		$('.listing-slider-for').slick({
			adaptiveHeight: true,
			arrows: false,
			asNavFor: '.listing-slider-nav',
			fade: true,
			slidesToScroll: 1,
			slidesToShow: 1
		});

		$('.listing-slider-nav').slick({
			arrows: false,
			asNavFor: '.listing-slider-for',
			focusOnSelect: true,
			slidesToScroll: 1,
			slidesToShow: 4,
			speed: 0,
			vertical: true,
		});

		$('.event-slider-for').slick({
			adaptiveHeight: true,
			arrows: false,
			asNavFor: '.event-slider-nav',
			fade: true,
			slidesToScroll: 1,
			slidesToShow: 1
		});

		$('.event-slider-nav').slick({
			arrows: false,
			asNavFor: '.event-slider-for',
			focusOnSelect: true,
			slidesToScroll: 1,
			slidesToShow: 4,
			speed: 0,
			vertical: true,
		});
	} else {
		$('.listing-slider-for').slick({
			adaptiveHeight: true,
			arrows: false,
			slidesToScroll: 1,
			slidesToShow: 1
		});

		$('.event-slider-for').slick({
			adaptiveHeight: true,
			arrows: false,
			slidesToScroll: 1,
			slidesToShow: 1
		});
	}

	function load_next_page() {

		var self = $(this),
			container = self.closest('.block'),
			offset = container.data('offset');

		$('body').addClass('il-loading-content');

		container.find('.il-load-more').remove();
		container.append( hc_strings.loading );

		$.ajax({
			url: ajax_object.ajaxurl,
			type: 'POST',
			data: {
				action: 'hc_get_home_next_page_html',
				offset: offset
			},
			dataType: 'html',
			success: function( html ) {
				container.find('.il-loading').remove();
				container.append( html );

				container.data( 'offset', offset + 8 );
				maybe_add_load_more_button( container );

				$('body').removeClass('il-loading-content');
			}
		});

	}

	function maybe_add_load_more_button( container ) {

		if( container.data('offset') < container.data('total') )
			container.append( hc_strings.more_button );

	}

	maybe_add_load_more_button( $('.home-section-posts .block') );

	$('.home-section-posts').on( 'click', '.il-load-more', load_next_page );

})( window.jQuery );