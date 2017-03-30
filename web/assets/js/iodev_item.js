/*
 cloud::iodev_item.js
 
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

function getAjaxUrl(itempath, fnc) {
	
	 if ( itempath == undefined 
		      || itempath.length == 0 )
			 return 'unknown';
	 
	 return itempath.replace(/\/view.*$/, "/ajax/"+fnc);
}


function setItem(save_btn, data_sel, txt_sel, detail_sel, action) {
	
  	 var id = $(detail_sel).data('id');
   	 var value = $(data_sel).val();
   	
   	
	 if ( value == undefined
		  || id == undefined 
	      || id.length == 0 
	      || id == 0 )
		 return;
	
    var pl = save_btn.data('preloader');
    pl = $('#'+pl);
    
    save_btn.hide();
    pl.show();
    
    
 	$.ajax( {type: "POST", url: getAjaxUrl($(detail_sel).data('item-path'), action), data: JSON.stringify({ value: value })} )
    .done(function( response ) {   
    	
    	pl.hide();
    	save_btn.show();
    	
    	if ( response.success ) {
			if ( txt_sel != '' ) {
			}

    	}
    	
     	new PNotify({
            title: response.flash.title,
            text: response.flash.message,
            type: response.flash.type
 	    });
    });
 	
}

function selectNewLocation() {
	
	var clicked_id = $(this).data('id');
	$(this).find(':checkbox').prop('checked', true);
		
	$(this).parent('.row').find('label').each(function () { 
		
		if ( $(this).data('id') != clicked_id ) {
			$(this).find(':checkbox').prop('checked', false);
		}
	});

	$('form[name="_change_location_type"]').find('input[name="selected_id"]').val(clicked_id);
}

function loadLocationList() {
	var loc_list = $('.location-list');
	
	if ( loc_list.length == 0)
	  return;
	
	
	loc_list.html('<div class="gmb-loader"><div></div><div></div><div></div></div>');
	
	$.ajax( {type: "POST", url: $(loc_list).data('ajax-path') } )
    .done(function( response ) {      
    	
    	if ( response.success ) {

    		loc_list.hide();
    		loc_list.html(response.html);
    		loc_list.fadeIn();
    		
    		$('.iodev-change-location label').on('click', selectNewLocation);
    		
    		$( ".overlay-close" ).click(function() {
    			$( ".overlay" ).removeClass
    			( 'overlay-open' ); 
    		});
 
    	
    	} else {
    		loc_list.html('');
    		location.href = location.href;
    	}
    	
    });
    
   
}

$(document).ready(function(){
	
	
	$( "#overlay-delete" ).click(function() {
		 $( ".overlay-delete" ).addClass
		 ('overlay-open');
	});
		
	$( ".overlay-delete-close" ).click(function() {
		$( ".overlay-delete" ).removeClass
		( 'overlay-open' ); 
	});
	
	
	$( "#overlay-changelocation" ).click(function() {
		loadLocationList();
		 $( ".overlay-changelocation" ).addClass('overlay-open');
	});
	$( ".overlay-close" ).click(function() {
		$( ".overlay-changelocation" ).removeClass( 'overlay-open' );
	});
	

    $("#caption-save").click(function() {
    	setItem($(this), '#caption', '.caption_value', getDetailId(), 'setcaption');
    });
    
    $("#password-save").click(function() {
    	setItem($(this), '#password', '.password_value', getDetailId(), 'setpwd');
    });
    
    $("#comment-save").click(function() {
    	setItem($(this), '#comment_value', '', getDetailId(), 'setcomment');
    });
  });
  
 $(function () {
  $('[data-toggle="tooltip"]').tooltip()
  
  $('.supla-tooltip').tooltipster({
	  contentAsHTML: true,
	  touchDevices: true,
	  interactive: true,
	  position: 'left',
  });
});
	$( function() {
		
	    // init Isotope
		var isotope_init = function(e) {

			var $grid = $('.channels').isotope({
				itemSelector: '.element-item',
				layoutMode: 'fitRows'
			  });
			
			return $grid;
		}

		var $grid = null;
		
		if ( detectIE() == false ) {
			$grid = isotope_init();
		} else {
			$('.channels').hide();
			
			$(document).ready(function() {
		    	setTimeout(
		  			  function() {
		  				$('.channels').show();
		  				$grid = isotope_init();
		  				
		  			  }, 500);
			});
		}
	  
	  
	  // filter functions
	  var filterFns = {
		// show if number is greater than 50
		numberGreaterThan50: function() {
		  var number = $(this).find('.number').text();
		  return parseInt( number, 10 ) > 50;
		},
		// show if name ends with -ium
		ium: function() {
		  var name = $(this).find('.name').text();
		  return name.match( /ium$/ );
		}
	  };
	  // bind filter button click
	  $('.btn-group').on( 'click', 'button', function() {
		var filterValue = $( this ).attr('data-filter');
		// use filterFn if matches value
		filterValue = filterFns[ filterValue ] || filterValue;
		$grid.isotope({ filter: filterValue });
	  });
	  // change is-checked class on buttons
	  $('.btn-group').each( function( i, buttonGroup ) {
		var $buttonGroup = $( buttonGroup );
		$buttonGroup.on( 'click', 'button', function() {
		  $buttonGroup.find('.active').removeClass('active');
		  $( this ).addClass('active');
		});
	  });
	 
	  
	});
