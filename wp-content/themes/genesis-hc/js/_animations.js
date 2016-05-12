(function($) {

	$('.animation').bind("webkitAnimationEnd mozAnimationEnd animationend", function() {
		$(this).removeClass('run');
	});

	function reset_animation( el ) {

		el.removeClass('run');
		el.addClass('run');

	}

	$('.main-menu .menu-col-links i').on(
		'mouseenter',
		function() {
			reset_animation( $(this) );
		}
	);

	$(window).on(
		'load',
		function() {
			$('.event-slider-nav .slick-slide, .event-slider-for .slide-content, .slider-nav .slick-slide, .subcategory-description, .archive-entry-small').on(
				'mouseenter',
				function() {
					console.log($(this).find('.category-icon'));
					reset_animation( $(this).find('.category-icon') );
				}
			);
		}
	);

})( window.jQuery );
