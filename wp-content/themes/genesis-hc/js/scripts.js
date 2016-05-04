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
		midClick: true,
		iframe: {
			markup: '<div class="mfp-iframe-scaler">'+
						'<div class="mfp-close"></div>'+
					'</div>',
			patterns: {
				youtube: {
					index: 'youtube.com',
					id: 'v=',
					src: '//www.youtube.com/embed/%id%?autoplay=1&enablejsapi=1'
				}
			}
		},
		callbacks: {
			open: function() {
				var embed_src = this.currItem.src,
					is_youtube = false,
					video_id,
					html;

				$.each( this.st.iframe.patterns, function() {
					if( embed_src.indexOf( this.index ) > -1) {
						if( this.id ) {
							if( typeof this.id === 'string' ) {
								embed_src = embed_src.substr( embed_src.lastIndexOf(this.id) + this.id.length, embed_src.length);
							} else {
								embed_src = this.id.call( this, embed_src );
							}
						}

						if( 'youtube.com' === this.index ) {
							is_youtube = true;
							video_id = embed_src;
							embed_src = this.src.replace(/%id%/g, embed_src );
						} else {
							embed_src = this.src.replace('%id%', embed_src );
						}

						return false;
					}
				});

				if( is_youtube ) {
					html = '<iframe id="youtube-player-' + video_id + '" src="' + embed_src  + '" class="mfp-iframe youtube-tracked-embed" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
				} else {
					html = '<iframe src="' + embed_src  + '" class="mfp-iframe" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
				}

				this.container.find( '.mfp-iframe-scaler' ).append( html );

				if( is_youtube )
					$('body').trigger( 'hc_load_youtube_modal_video' );
			}
		}
	});

	// HTML Popup
	// https://stackoverflow.com/questions/19990191/select2-in-magnific-popup-search-input-cannot-be-focused
	$.magnificPopup.instance._onFocusIn = function(e) {
		return true;
	};

	$('.open-popup-link').magnificPopup({
		type: 'inline',
		midClick: true,
		callbacks: {
			change: function() {
				var redirect = this.items[ this.index ].el.data( 'redirect' );

				if( redirect )
					$(this.contentContainer).find('form').data( 'redirect', redirect );

				if( $(this.contentContainer).find( '.btn-facebook' ).length > 0 )
					hc_maybe_load_facebook();

				if( $(this.contentContainer).find( '.captcha' ).length > 0 )
					hc_init_captcha( $(this.contentContainer) );
			}
		}
	});

	// Entry header slideshow
	$('.entry-slideshow').slick({
		prevArrow: hc_strings.prev_arrow,
		nextArrow: hc_strings.next_arrow,
		centerMode: true,
		slidesToShow: 1,
		speed: 500,
		variableWidth: true,
		responsive: [
			{
				breakpoint: im.getValue('phone', true),
				settings: {
					variableWidth: false,
				}
			}
		]
	});

	// Events/listings slideshow
	$('.basic-slider').slick({
		prevArrow: hc_strings.prev_arrow,
		nextArrow: hc_strings.next_arrow,
		speed: 500,
		adaptiveHeight: true,
	});

	// Footer IG images
	var exclude_mobile_images = im.lessThan('portrait');
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

	// Buttons nav
	$('body').on( 'click', '.button-nav > .btn', function() {
		var self = $(this);

		if( self.hasClass('use-modal') ) {
			$.magnificPopup.open({
				items: {
					src: self.data('modal-html')
				},
				type: 'inline',
				showCloseBtn: false
			});
		} else {
			self.closest('.button-nav').toggleClass('open');
		}
	});

})( window.jQuery );
