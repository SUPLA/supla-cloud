/*
	By Osvaldas Valutis, www.osvaldas.info
	Available for use under the MIT License
*/

;( function( $, window, document, undefined )
{
	'use strict';
	$.fn.addClassWhenEmail = function( options )
	{
		options = $.extend(
					{
						email:		'input[type="email"]',
						className:	'active'
					},
					options );

		$( this ).each( function()
		{
			var $form	= $( this ),
				$email	= $form.find( options.email ),
				val		= '';

			$email.on( 'keyup.addClassWhenEmail', function()
			{
				val = $email.val();
				$form.toggleClass( options.className, val != '' && /^([\w-\.]+@([\w-]+\.)+[\w-]{2,12})?$/.test( val ) );
			});
		});

		return this;
	};
})( jQuery, window, document );