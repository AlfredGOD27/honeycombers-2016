(function($) {

	function add_message( container, status, message ) {

		if( 'error' === status ) {
			container.html( '<div class="alert alert-' + status + ' animated shake">' + message + '</div>' );
		} else {
			container.html( '<div class="alert alert-' + status + '">' + message + '</div>' );
		}

	}

	$('#register-popup form').on( 'submit', function(e) {
		e.preventDefault();

		var self = $(this);

		$.ajax({
			url: ajax_object.ajaxurl,
			type: 'POST',
			data: {
				action: 'hc_ajax_register',
				email: self.find('[name="email"]').val(),
				password: self.find('[name="password"]').val()
			},
			success: function( json ) {
				var data = JSON.parse( json );

				add_message( self.closest('.white-popup').find('.messages'), data.status, data.message );

				if( 'success' === data.status ) {
					self.find('input, button').prop('disabled', true);

					setTimeout(
						function() {
							window.location.href = data.redirect_to;
						},
						1500
					);
				}
			}
		});

		return false;
	});


	$('#login-popup form').on( 'submit', function(e) {
		e.preventDefault();

		var self = $(this);

		$.ajax({
			url: ajax_object.ajaxurl,
			type: 'POST',
			data: {
				action: 'hc_ajax_login',
				log: self.find('[name="log"]').val(),
				pwd: self.find('[name="pwd"]').val(),
				rememberme: self.find('[name="rememberme"]').prop('checked')
			},
			success: function( json ) {
				console.log(json);
				var data = JSON.parse( json );

				add_message( self.closest('.white-popup').find('.messages'), data.status, data.message );

				if( 'success' === data.status ) {
					self.find('input, button').prop('disabled', true);

					setTimeout(
						function() {
							window.location.href = data.redirect_to;
						},
						1500
					);
				}
			}
		});

		return false;
	});

	$('#password-popup form').on( 'submit', function(e) {
		e.preventDefault();

		var self = $(this);

		$.ajax({
			url: ajax_object.ajaxurl,
			type: 'POST',
			data: {
				action: 'hc_ajax_reset_password',
				email: self.find('[name="user_login"]').val()
			},
			success: function( json ) {
				var data = JSON.parse( json );

				add_message( self.closest('.white-popup').find('.messages'), data.status, data.message );

				if( 'success' === data.status )
					self.find('input, button').prop('disabled', true);
			}
		});

		return false;
	});

	$('.btn-facebook').on( 'click', function(e) {
		e.preventDefault();

		var self = $(this);

		FB.login(
			function(response) {

				if( response.status === 'connected' ) {
					$.ajax({
						url: ajax_object.ajaxurl,
						type: 'POST',
						data: {
							action: 'hc_ajax_facebook_register_or_login',
							token: response.authResponse.accessToken
						},
						success: function( json ) {
							var data = JSON.parse( json );

							add_message( self.closest('.white-popup').find('.messages'), data.status, data.message );

							if( 'success' === data.status ) {
								self.find('input, button').prop('disabled', true);

								setTimeout(
									function() {
										window.location.href = data.redirect_to;
									},
									1500
								);
							}
						}
					});
				} else if( response.status === 'not_authorized' ) {
					add_message( self.closest('.white-popup').find('.messages'), 'error', 'You must authorize the app to login via Facebook.' );
				} else {
					add_message( self.closest('.white-popup').find('.messages'), 'error', 'You must login to Facebook.' );
				}
			},
			{
				scope: 'public_profile,email'
			}
		);
	});

})( window.jQuery );
