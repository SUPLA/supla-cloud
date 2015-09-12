/*
 cloud::iodev.js
 Przemyslaw Zygmunt <p.zygmunt@acsoftware.pl>
 Mateusz Major <mateuszmajor@me.com>
 
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 3
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

$( function() {
	// quick search regex
	var qsRegex;
	var buttonFilter;
	var buttonconnectionFilter;
	
	// init Isotope
	var $container = $('.devices').isotope({
	  itemSelector: '.device',
	  layoutMode: 'fitRows',
	  filter: function() {
		var $this = $(this);
		var searchResult = qsRegex ? $this.text().match( qsRegex ) : true;
		var buttonResult = buttonFilter ? $this.is( buttonFilter ) : true;
		var buttonconnectionResult = buttonconnectionFilter ? $this.is( buttonconnectionFilter ) : true;
		return searchResult && buttonResult && buttonconnectionResult;
	  }
	});
  
	$('#filters').on( 'click', 'button', function() {
	  buttonFilter = $( this ).attr('data-filter');
	  $container.isotope();
	});
	
	$('#filtersconnection').on( 'click', 'button', function() {
	  buttonconnectionFilter = $( this ).attr('data-filter');
	  $container.isotope();
	});

	
	// use value of search field to filter
	var $quicksearch = $('#quicksearch').keyup( debounce( function() {
	  qsRegex = new RegExp( $quicksearch.val(), 'gi' );
	  $container.isotope();
	}) );
  
	
	  // change active class on buttons
	$('.btn-group').each( function( i, buttonGroup ) {
	  var $buttonGroup = $( buttonGroup );
	  $buttonGroup.on( 'click', 'button', function() {
		$buttonGroup.find('.active').removeClass('active');
		$( this ).addClass('active');
	  });
	});
	
  });
  
  // debounce so filtering doesn't happen every millisecond
  function debounce( fn, threshold ) {
	var timeout;
	return function debounced() {
	  if ( timeout ) {
		clearTimeout( timeout );
	  }
	  function delayed() {
		fn();
		timeout = null;
	  }
	  setTimeout( delayed, threshold || 100 );
	};
  }
	