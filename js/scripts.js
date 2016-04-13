// Avoid `console` errors in browsers that lack a console.
// http://html5boilerplate.com/
(function() {
	var method;
	var noop = function () {};
	var methods = [
		'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
		'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
		'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
		'timeStamp', 'trace', 'warn'
	];
	var length = methods.length;
	var console = (window.console = window.console || {});

	while( length-- ) {
		method = methods[length];

		// Only stub undefined methods.
		if( !console[method] ) {
			console[method] = noop;
		}
	}
}());

(function($) {

	// Remove the 'no-js' <body> class
	$('html').removeClass('no-js');

	// Enable FitVids on the content area
	$('.content').fitVids();

	// Video Popup
	$('.open-video-link').magnificPopup({
		type: 'iframe',
		midClick: true
	});

	// HTML Popup
	$('.open-popup-link').magnificPopup({
		type: 'inline',
		midClick: true,
		callbacks: {
			open: function() {
				var item = $(this.contentContainer).find( 'input:visible' );
				if( item.length > 0 ) {
					setTimeout(
						function() {
							item.eq(0).focus();
						},
						50
					);
				}

				if( $(this.contentContainer).find( '.btn-facebook' ).length > 0 )
					hc_maybe_load_facebook();
			}
		}
	});

	// Entry header slideshow
	$('.entry-slideshow').slick({
		prevArrow: '<button type="button" class="slick-prev" title="Previous"><i class="ico-arrow-left"></i></button>',
		nextArrow: '<button type="button" class="slick-next" title="Next"><i class="ico-arrow-right"></i></button>',
		centerMode: true,
		slidesToShow: 1,
		variableWidth: true,
	});

	// Footer IG images
	var exclude_mobile_images = im.lessThan('tablet');
	$('.async-load-image').each( function() {
		var placeholder = $(this),
			data,
			el;

		if( exclude_mobile_images && placeholder.hasClass('skip-image-on-mobile') ) {

		} else {
			data = placeholder.data();
			el = document.createElement('img');
			$.each( data, function(att, value) {
				att = att.replace( 'data-', '' );
				el.setAttribute( att, value );
			});

			placeholder.after( el );
			placeholder.remove();
		}
	});

})( window.jQuery );
