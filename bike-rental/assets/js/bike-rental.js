/**
 * Bike Rental — front-end behaviour.
 *
 * Keeps the booking form's end date in sync with the chosen start date.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var forms = document.querySelectorAll( '.bike-rental-form' );

		Array.prototype.forEach.call( forms, function ( form ) {
			var start = form.querySelector( '#bike_rental_start' );
			var end = form.querySelector( '#bike_rental_end' );

			if ( ! start || ! end ) {
				return;
			}

			function syncEndDate() {
				if ( start.value ) {
					end.min = start.value;
					if ( end.value && end.value < start.value ) {
						end.value = start.value;
					}
				}
			}

			start.addEventListener( 'change', syncEndDate );
			syncEndDate();
		} );
	} );
}() );
