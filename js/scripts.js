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

	// SVG fallbacks
	svgeezy.init( 'svg-no-check', 'png' );

	// Support for HTML5 placeholders
	$('input, textarea').placeholder();

	// Entry header slideshow
	$('.entry-slideshow').slick({
		prevArrow: '<button type="button" class="slick-prev" title="Previous"><i class="ico-arrow-left"></i></button>',
		nextArrow: '<button type="button" class="slick-next" title="Next"><i class="ico-arrow-right"></i></button>',
		centerMode: true,
		slidesToShow: 1,
		variableWidth: true,
	});

	// Footer IG images
	var ig_images_to_show = !im.lessThan('tablet') ? 7 : 3,
		i = 1;
	$('.footer-social-row .right > div').each( function() {
		var self = $(this),
			img = self.find('img');

		if( i <= ig_images_to_show ) {
			img.attr( 'src', img.data('src') );
		} else {
			self.remove();
		}

		i++;
	});

})( window.jQuery );
