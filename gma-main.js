( function( $ ){

	$( '.gma-count' ).html( gma_globals.gma_likes );

	// Main click() method
	$( '.gma-like' ).click( function(){

		event.preventDefault();

		// Perform an asynchronous HTTP (Ajax) request: https://api.jquery.com/jquery.ajax/
		$.ajax({
			type: 'post',
			dataType: 'json',
			url: gma_globals.ajax_url,
			data: {
				//action: 'gma_add_like',
				action: gma_globals.action,
				_ajax_nonce: gma_globals.nonce
			},
			success: function( response ){

				if ( 'success' == response.type ) {
					
					$('.gma-count').html( response.gma_total_likes );
				
				} else {
					// TODO: Add WC error logging
					console.log( 'Nope!' );
				}
			}

		}); // <-- End of $.ajax()
	} ); // <-- End of $.click()

} )( jQuery );