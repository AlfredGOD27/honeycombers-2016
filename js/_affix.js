(function($) {

	if( typeof document.addEventListener !== 'function' )
		return;

	// Setup global vars
	var affix_on = false,
		widget,
		widget_height,
		widget_distance,
		sidebar_width,
		sidebar_height,
		lastScrollY = 0,
		ticking = false,
		timer;

	function init() {

		widget = $('.sidebar .affix-on-scroll');

		// Update measurements
		widget_height = widget.height();
		widget_distance = widget.offset().top;
		sidebar_width = widget.closest('.sidebar').width();
		sidebar_height = widget.closest('.sidebar').height();

		// Assume affix is on
		affix_on = true;

		if( widget_distance + (widget_height * 2 ) > sidebar_height ) {
			// Turn off if sidebar isn't tall enough
			affix_on = false;
		} else {
			// Turn off is window isn't fullwidth
			if( !im.greaterThan('portrait') )
				affix_on = false;
		}

		if( affix_on ) {
			// If so, lock widget width
			widget.css( 'width', sidebar_width );
		} else {
			// If off, undo any modifications
			widget.css( 'width', 'auto' );
			widget.css( 'top', 'auto' );
			widget.removeClass('affix');
			widget.removeClass('affix-bottom');
		}

	}

	function on_scroll() {
		lastScrollY = window.scrollY;

		if( affix_on )
			request_tick();
	}

	function request_tick() {
		if( ticking )
			return;


		ticking = true;

		clearTimeout(timer);
		timer = setTimeout(
			function() {
				requestAnimationFrame(update_affix);
			},
			20
		);
	}

	function update_affix() {
		var header_height = $('.site-top').outerHeight() + $('.header-navigation-container').outerHeight(),
			offset_height = $('.sticky-header').outerHeight() + $('#wpadminbar').outerHeight(),
			footer_offset = $('.site-footer').offset().top,
			widget_offset = lastScrollY - $('.sidebar').offset().top + offset_height + 16 - $('.sidebar-widgets').height();

		if( widget_distance < lastScrollY + offset_height ) {
			widget.addClass('affix');
			widget.css( 'transform', 'translateY(' + widget_offset + 'px)' );

			if( lastScrollY + widget_height + header_height > footer_offset ) {
				widget.addClass('affix-bottom');
				widget.css( 'transform', 'translateY(0)' );
			} else {
				widget.removeClass('affix-bottom');
			}
		} else {
			widget.removeClass('affix');
			widget.css( 'transform', 'translateY(0)' );
		}

		ticking = false;
	}

	$(window).on( 'load', function() {
		var widgets = $('.sidebar').find('.widget_hc_follow_widget, .widget_hc_subscribe_widget');

		if( 0 === widgets.length )
			return;

		widgets.wrapAll('<div class="affix-on-scroll" />');
		$('.sidebar > .widget').wrapAll('<div class="sidebar-widgets" />');
		$('body').addClass('has-affixed-sidebar');

		init();
		on_scroll();

		window.addEventListener('scroll', on_scroll, false);
		$(window).on( 'resize', init );
	});

})( window.jQuery );
