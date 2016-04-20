(function($) {

	$('.slider-for').on( 'init', function() {
		$(this).fitVids();
	});

	if( !im.lessThan('tablet') ) {
		$('.slider-for').slick({
			adaptiveHeight: true,
			arrows: false,
			asNavFor: '.slider-nav',
			fade: true,
			slidesToScroll: 1,
			slidesToShow: 1
		});

		$('.slider-nav').slick({
			arrows: false,
			asNavFor: '.slider-for',
			focusOnSelect: true,
			slidesToScroll: 1,
			slidesToShow: 4,
			speed: 0,
			useCSS: false,
			vertical: true,
		});
	} else {
		$('.slider-for').slick({
			adaptiveHeight: true,
			arrows: true,
			slidesToScroll: 1,
			slidesToShow: 1,
			prevArrow: hc_strings.prev_arrow,
			nextArrow: hc_strings.next_arrow,
		});
	}

})( window.jQuery );
