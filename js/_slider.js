(function($) {

	if( !$('body').hasClass('archive-has-slider') && !$('body').hasClass('page-template-page_calendar') )
		return;

	$('.slider-for').on( 'init', function() {
		$(this).fitVids();
	});

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
		vertical: true
	});

})( window.jQuery );
