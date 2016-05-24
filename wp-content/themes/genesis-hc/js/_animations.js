(function($) {

	$('.animation').bind("webkitAnimationEnd mozAnimationEnd animationend", function() {
		$(this).removeClass('run');
	});

	function reset_animation( el ) {

		el.removeClass('run');
		el.addClass('run');

	}

	$(window).on(
		'load',
		function() {
			$('.event-slider-nav .slick-slide, .event-slider-for .slide-content, .slider-nav .slick-slide, .subcategory-description, .archive-entry-small, .home-section-trending a, .main-menu .menu-item.has-children').on(
				'mouseenter',
				function() {
					reset_animation( $(this).find('.category-icon') );
				}
			);
		}
	);

})( window.jQuery );
