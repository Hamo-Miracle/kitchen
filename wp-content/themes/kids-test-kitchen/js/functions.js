;(function($, window, document, undefined) {
	var $win = $(window);
	var $doc = $(document);

	$doc.ready(function() {
		$('.menu-toggle').on('click', function() {
			$('#menu-main-nav').toggle();
		});

		$('.main-navigation .menu-item-has-children > a').on('click', function(e) {
			if ( $win.width() > 767 ) {
				return;
			}

			if ( ! $(this).parent().hasClass('expanded') ) {
				e.preventDefault();

				$(this)
					.parent()
					.addClass('expanded')
					.siblings()
					.removeClass('expanded');
			}
		});

	});
})(jQuery, window, document);
