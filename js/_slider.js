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
			vertical: true,
		});
	} else {
		$('.slider-for').slick({
			adaptiveHeight: true,
			arrows: true,
			slidesToScroll: 1,
			slidesToShow: 1,
			prevArrow: '<button type="button" class="slick-prev" title="Previous"><i class="ico-arrow-left"></i></button>',
			nextArrow: '<button type="button" class="slick-next" title="Next"><i class="ico-arrow-right"></i></button>',
		});
	}

})( window.jQuery );
