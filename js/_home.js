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
	} else {
		$('.listing-slider-for').slick({
			adaptiveHeight: true,
			arrows: false,
			slidesToScroll: 1,
			slidesToShow: 1
		});
	}

})( window.jQuery );
