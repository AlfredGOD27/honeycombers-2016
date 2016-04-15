var captcha_init = false;

window.hc_activate_captcha = function() {

	if( 'undefined' === typeof $.magnificPopup.instance.contentContainer )
		return;

	$.magnificPopup.instance.contentContainer.find('.captcha:not(.loaded)').each( function() {
		var self = $(this);

		grecaptcha.render(
			self[0],
			{
				'sitekey': hc_settings.recaptcha_key,
				'callback': function(response) {
					self.data( 'captcha-response', response );
					self.closest('form').find('[type="submit"]').prop( 'disabled', false );
				},
				'expired-callback': function() {
					self.data( 'captcha-response', '' );
					self.closest('form').find('[type="submit"]').prop( 'disabled', true );
				}
			}
		);

		self.addClass('loaded');
	});

};

function hc_init_captcha() {

	if( captcha_init ) {
		hc_activate_captcha();
		return;
	}

	captcha_init = true;

	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = 'https://www.google.com/recaptcha/api.js?onload=hc_activate_captcha&render=explicit';

	document.getElementsByTagName('head')[0].appendChild(script);

}
