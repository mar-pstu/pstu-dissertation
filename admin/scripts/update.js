jQuery( document ).ready( function () {

	let $form = jQuery( '#options-update-form' );
	let $result = jQuery( '#options-update-result' );

	if ( typeof $form.length != 'undefined' && $form.length > 0 ) {
		$form.on( 'submit', run_update );
	}

	function run_update( event ) {
		event.preventDefault();
		var $form = jQuery( this );
		var data = $form.serializeArray();
		jQuery.post( ajaxurl, data, function( response, textStatus, jqXHR ) {
			console.log( response );
			if ( typeof response.data == 'undefined' || typeof response.data.done == 'undefined' || response.data.done ) {
				$result.html( response.data.message );
				setTimeout( function () {
					window.location = window.location.href;
				}, 2500 );
			} else {
				$result.html( response.data.message );
				$form.submit();
			}
		} );
	}

} );