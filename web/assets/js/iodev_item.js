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



function setEnabled(src_sel, dst_sel, detail_sel, enabled) {
	
	
	if ( $(src_sel) == undefined
		  || $(dst_sel) == undefined
		  || $(detail_sel) == undefined )
		return;
	
	 var id = $(detail_sel).data('id');

	 if ( id == undefined 
	      || id.length == 0 
	      || id == 0 )
		 return;
	
	 $(src_sel).hide();
     
     var pl = $(src_sel).data('preloader');
     pl = $('#'+pl);
     pl.show();
     

 	$.ajax( {type: "POST", url: getAjaxUrl($(detail_sel).data('item-path'), 'setenabled/'+enabled) } )
     .done(function( response ) {      
     	
     	if ( response.success ) {
     		
     		
 
    	    pl.hide();
   		    $(dst_sel).show();
     		
     		
     	} else {
     		
             if ( pl.length != 0 )
             	pl.hide();
             
             $(src_sel).show();
     	}
     	
     	new PNotify({
                title: response.flash.title,
                text: response.flash.message,
                type: response.flash.type
     	});
     	
     });
	
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

$(document).ready(function(){
	
	
	$( "#overlay-delete" ).click(function() {
		 $( ".overlay-delete" ).addClass
		 ('overlay-open');
	});
		
	$( ".overlay-delete-close" ).click(function() {
		$( ".overlay-delete" ).removeClass
		( 'overlay-open' ); 
	});
	
	
	$( "#overlay-assignments" ).click(function() {
		 $( ".overlay-assignments" ).addClass('overlay-open');
	});
	$( ".overlay-close" ).click(function() {
		$( ".overlay-assignments" ).removeClass( 'overlay-open' );
	});
	
	   
    
    $(".btn-enable").click(function() {  	
    	setEnabled('.btn-enable', '.btn-disable', getDetailId(), '0');
    });
    
    $(".btn-disable").click(function() {
    	setEnabled('.btn-disable', '.btn-enable', getDetailId(), '1');
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