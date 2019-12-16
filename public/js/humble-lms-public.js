(function( $ ) {
	'use strict'

	/**
	 * AJAX
	 * 
	 * Calls an AJAX callback for the site front-end.
	 * Add a button with class ".ajax" to test.
	 */
	$(document).on('submit', '#humble-lms-mark-complete', function (e) {
    e.preventDefault();

		$.ajax({
      url: humble_lms.ajax_url,
      type: 'POST',
      data: {
        action: 'mark_lesson_complete',
        nonce: humble_lms.nonce
      },
      dataType: 'json',
			error: function(MLHttpRequest, textStatus, errorThrown) {
				console.error(errorThrown);
			},
			success: function(response, textStatus, XMLHttpRequest) {
				console.log( response )
			},
			complete: function(reply, textStatus) {
				console.log( textStatus )
			}
		})
  })
  
})( jQuery );
