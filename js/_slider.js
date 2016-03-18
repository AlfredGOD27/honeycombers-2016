(function($) {

	if( !$('body').hasClass('archive-sub-sections') )
		return;

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
		centerMode: true,
		focusOnSelect: true,
		slidesToScroll: 1,
		slidesToShow: 3,
		vertical: true
	});

})( window.jQuery );
