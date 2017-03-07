/*
 cloud::details.js
 
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


function checkAll(e) {
	
  	var checked = e.prop('checked');
	var tbl = e.closest('table');
	if ( tbl ) {
        var tbody = tbl.find('tbody');
        if ( tbody ) {
        	var checkboxes = tbody.find(":checkbox");
        	checkboxes.prop('checked', checked);
        }
	}
}

function loadAssignList() {
	var loc_aid = $('.assign-list');
	var detail = $(getDetailId());
	
	if ( loc_aid.length == 0 || detail.length == 0 )
	return;
	
	var aid = detail.data('id');
	
	loc_aid.html('<div class="gmb-loader"><div></div><div></div><div></div></div>');

	$.ajax( {type: "POST", url: getAjaxUrl($(detail).data('item-path'), 'assign_list') } )
    .done(function( response ) {      
    	
    	if ( response.success ) {

    		loc_aid.hide();
    		loc_aid.html(response.html);
    		loc_aid.fadeIn();
    		
    		$('[name="tbl-checkall"]').bind('click', function() {
    			  checkAll($(this));
    		});
    		
    		$( ".overlay-close" ).click(function() {
    			$( ".overlay" ).removeClass
    			( 'overlay-open' ); 
    		});
    		
    		
    	} else {
    		loc_aid.html('');
    		location.href = location.href;
    	}
    	
    });
   
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
     		
			$(document).find('.scroll_list_wrapper a').each(function (index, element) {
				if ( $(this).data('id') == id ) {
					
					if ( enabled == '1' ) {
						$(this).removeClass('disabled');
						$(this).addClass('enable');
					} else {
						$(this).removeClass('enable');
						$(this).addClass('disabled');
					}
					
					var status = $(this).find('.status');
	
					if ( status != undefined ) {

						status.html(response.value);
						
						if ( enabled == '1' ) {
							status.addClass('enable');		
							status.removeClass('disable');
						} else {
							status.addClass('disable');	
							status.removeClass('enable');
						}
					}
					
					return true;
				}
			});
     		
 
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
				$(document).find('.scroll_list_wrapper a').each(function (index, element) {
					if ( $(this).data('id') == id ) {
						
		
						var val = $(this).find(txt_sel);
						
						if ( val != undefined && val.length > 0 ) {
							val.html(value);
						}
							
						
						return true;
					}
	        	});
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
	$("#accessid-detail").fadeIn(700);
	$("#details").fadeIn(700);
	
		
	$( "#overlay-assignments" ).click(function() {
		loadAssignList();
		 $( ".overlay-assignments" ).addClass('overlay-open');
		 	 $('body').attr('style', 'overflow: hidden');
			 $( "#assign_type_cancel" ).click(function() {
				$('body').attr('style', 'overflow: visible');
			 });
	});
	
	
	$( "#overlay-delete" ).click(function() {
		 $( ".overlay-delete" ).addClass
		 ('overlay-open');
	});
		
	$( ".overlay-delete-close" ).click(function() {
		$( ".overlay-delete" ).removeClass
		( 'overlay-open' ); 
	});
	
	if ( $( "#generate" ).length ) {
		
	    $('#generate').pGenerator({
			  'bind': 'click',
			  'passwordElement': '',
			  'displayElement': '#password',
			  'passwordLength': 8,
			  'uppercase': true,
			  'lowercase': true,
			  'numbers':   true,
			  'specialChars': false,
			  'onPasswordGenerated': function(generatedPassword) {
				  $('#password').focus();
			   }
		  });
	    
	}
	
	if ( $( "#generate-short" ).length ) {
	  
	    $('#generate-short').pGenerator({
		  'bind': 'click',
		  'passwordElement': '',
		  'displayElement': '#password',
		  'passwordLength': 4,
		  'uppercase': true,
		  'lowercase': true,
		  'numbers':   true,
		  'specialChars': false,
		  'onPasswordGenerated': function(generatedPassword) {
			  $('#password').focus();
		   }
	  });
	    
	}
	   
    
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
	 
	 if ( $('.supla-tooltip').length ) {
		 
		  $('[data-toggle="tooltip"]').tooltip()
		  
		  $('.supla-tooltip').tooltipster({
			  contentAsHTML: true,
			  touchDevices: true,
			  interactive: true,
			  position: 'left',
		  });
		  
	 }
	 

  
  
});
