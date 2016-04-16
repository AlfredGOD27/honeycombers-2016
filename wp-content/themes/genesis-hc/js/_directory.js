function hc_directory_maps() {

	var $ = jQuery,
		els = {
			form: $('.directory-search-form'),
			results: $('.directory-search-results'),
			map: $('.directory-map')
		},
		map,
		loading = false,
		markers = [],
		open_info_window = false,
		map_settings = {
			zoom: 4,
			zoomControl: true,
			disableDefaultUI: true,
			scrollwheel: false,
			center: new google.maps.LatLng( hc_directory_coords.lat, hc_directory_coords.lng )
		},
		use_map = !im.lessThan('tablet');

	function reset() {

		// Close any open window
		if( open_info_window )
			open_info_window.close();

		open_info_window = false;

		// Remove any markers
		$.each( markers, function(idx, marker) {
			google.maps.event.clearInstanceListeners( marker );
			marker.setMap(null);
		});
		markers = [];

		// Clear results list
		els.results.html('');

	}

	function start_loading() {

		loading = true;
		els.results.html( hc_strings.loading );

		if( use_map )
			map.setCenter( map_settings.center );

		els.form.find('input[type="search"]').prop( 'readonly', true );
		els.form.find('button[type="submit"], select').prop( 'disabled', true );

	}

	function stop_loading() {

		loading = false;
		els.results.find('.il-loading').remove();
		els.form.find('input[type="search"]').prop( 'readonly', false );
		els.form.find('button[type="submit"], select').prop( 'disabled', false );

	}

	function add_marker( title, content, latitude, longitude ) {

		var info_window = new google.maps.InfoWindow({
				content: content,
				maxWidth: 150
			}),
			marker = new google.maps.Marker({
				position: new google.maps.LatLng( latitude, longitude ),
				map: map,
				title: title,
				animation: google.maps.Animation.DROP,
				icon: {
					path: 'M10,0C4.478,0,0,4.479,0,10c0,5.523,10,19.714,10,19.714S20,15.523,20,10C20,4.479,15.521,0,10,0z M10.001,15.714c-3.157,0-5.716-2.561-5.716-5.714c0-3.155,2.559-5.715,5.716-5.715c3.156,0,5.714,2.56,5.714,5.715 C15.715,13.156,13.157,15.714,10.001,15.714z',
					fillColor: '#F79534',
					fillOpacity: 1,
					strokeWeight: 0
				}
			});

		// Add marker listener to open info window
		google.maps.event.addListener( marker, 'click', function() {
			if( open_info_window )
				open_info_window.close();

			open_info_window = info_window;
			info_window.open(map, marker);
		});

		return marker;

	}

	function search( e ) {

		e.preventDefault();

		reset();
		start_loading();

		$.ajax({
			url: ajax_object.ajaxurl,
			type: 'POST',
			data: {
				action: 'hc_get_listings',
				text: els.form.find('input[type="search"]').val(),
				location_id: els.form.find('select[name="location"] option:selected').val(),
				category_id: els.form.find('select[name="category"] option:selected').val()
			},
			success: function( data ) {
				var bounds = new google.maps.LatLngBounds();

				// Parse server output
				data = JSON.parse(data);

				switch( data.status ) {
					case 'error':
					case 'info':
						els.results.html( '<div class="alert alert-' + data.status + '">' + data.message + '</div>' );
						break;
					case 'success':
						els.results.append( '<h2>' + data.heading + '</h2>' );

						// Add each marker
						$.each( data.items, function(_, item) {
							if( use_map && item.lat && item.lng ) {
								// Add to map
								marker = add_marker(
									item.name,
									item.info_window_html,
									item.lat,
									item.lng
								);

								// Add to array
								markers.push( marker );

								// Note position for later centering
								bounds.extend( marker.getPosition() );
							}

							// Add to results list
							els.results.append( item.result_html );
						});

						// Set new center, based on results
						if( use_map ) {
							map.setCenter( bounds.getCenter() );
							map.fitBounds( bounds );
						}
						break;
				}

				stop_loading();
			}
		});

	}

	// Activate map
	if( use_map )
		map = new google.maps.Map( els.map[0], map_settings );

	// Update on search
	els.form.on( 'submit', search );

}
