(function($) {

	$('.subscribe-form').on( 'submit', function(e) {
		e.preventDefault();

		var self = $(this),
			email_field = self.find('input[type="email"]'),
			email = email_field.val();

		if( self.hasClass('processing') )
			return;

		self.find('.result').remove();
		self.addClass('processing');
		$.ajax({
			url: ajax_object.ajaxurl,
			type: 'POST',
			data: {
				action: 'hc_ajax_subscribe',
				email: email
			},
			success: function( json ) {
				var data = JSON.parse(json);

				self.removeClass('processing');
				self.addClass(data.status);

				self.find('.email-container').append('<i title="' + data.message + '" class="result ' + data.status + '"></i>');

				if( 'success' === data.status ) {
					email_field.prop( 'readonly', true );
					self.find('button[type="submit"]').prop( 'disabled', true );
				}
			}
		});
	});

})( window.jQuery );

(function($){	
	function isValidEmailAddress(emailAddress) {
		var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
		return pattern.test(emailAddress);	
	};
	
	var email_input;
	
	$("#txt-email-foot").on("keyup change keydown", function() {
	   email_input = this.value;
	});
	
	var typingTimer;                //timer identifier
	var doneTypingIntervalFoot = 500;  //time in ms, 5 second for example
	var $input = $('#subscribeFormFoot');
	
	//on keyup, start the countdown
	$input.on('keyup', function () {
	  clearTimeout(typingTimer);
	  typingTimer = setTimeout(doneTypingFoot, doneTypingIntervalFoot);
	});
	
	//on keydown, clear the countdown
	$input.on('keydown', function () {
	  clearTimeout(typingTimer);
	});
	
	function doneTypingFoot () {
		if (!isValidEmailAddress(email_input)) {
			$("#subscribeFormFoot .email_error").show(); //error message
			$("#subscribeFormFoot #email").focus();   //focus on email field
		}
		else {
			$("#subscribeFormFoot .email_error").hide(); //error message
		}
	}
	
	$('#submitButtonFoot').click(function() {
		if (isValidEmailAddress($("#txt-email-foot").val())) {
			_btn.trackAccountSignup(email_input,{
				'newsletter_honeykidsasia':'TRUE', //ADD IN ADDITIONAL PROPERTIES HERE
			});
	
			$(".successMessageFoot").show();
			$("#subscribeFormFoot").hide();
		}
		else {
			$("#subscribeFormFoot .email_error").show();
		}
	});

})(jQuery);
