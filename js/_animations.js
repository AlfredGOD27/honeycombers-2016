(function($) {

<<<<<<< HEAD
	$('.animation').bind("webkitAnimationEnd mozAnimationEnd animationend", function() {
		$(this).removeClass('run');
	});
=======
	$('body').on(
		'webkitAnimationEnd mozAnimationEnd animationend',
		'.animation',
		function() {
			$(this).removeClass('run');
		}
	);
>>>>>>> refs/remotes/origin/cooper

	function reset_animation( el ) {

		el.removeClass('run');
		el.addClass('run');

	}

	$(window).on(
		'load',
		function() {
<<<<<<< HEAD
			$('.event-slider-nav .slick-slide, .event-slider-for .slide-content, .slider-nav .slick-slide, .subcategory-description, .archive-entry-small, .home-section-trending a, .main-menu .menu-item.has-children').on(
				'mouseenter',
=======
			$('body').on(
				'mouseenter',
				'.event-slider-nav .slick-slide, .event-slider-for .slide-content, .slider-nav .slick-slide, .subcategory-description, .archive-entry-small, .home-section-trending a, .main-menu .menu-item.has-children',
>>>>>>> refs/remotes/origin/cooper
				function() {
					reset_animation( $(this).find('.category-icon') );
				}
			);
		}
	);

})( window.jQuery );
