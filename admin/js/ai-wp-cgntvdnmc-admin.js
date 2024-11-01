(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */	
	
	function store_image_in_media_library() {
		$('.store-image-button').on('click', function() {
			var $this = $(this);
			var image_url = $this.data('url');
		
			$.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  data: {
				action: 'store_image_in_media_library',
				image_url: image_url
			  },
			  success: function(response) {
				//console.log(response);
				if (response.success === true) {
					console.log('Image stored successfully!');
					$this.parent().append('<p>Image stored successfully!</p>');
				}
			  }
			});
		});
	}

	$(document).ready(function () {
        store_image_in_media_library();
    });

	
})( jQuery );
