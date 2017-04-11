/*
 cloud::apisettings.js
 
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


$(document).ready(function(){

	$( "#overlay-delete" ).click(function() {
		 $( ".overlay-delete" ).addClass('overlay-open');
		 $("body" ).addClass('blur');
	});
	
	$( ".overlay-delete-close" ).click(function() {
		$( ".overlay-delete" ).removeClass( 'overlay-open' ); 
		 $("body" ).removeClass('blur');
	});
	
	$('#apiuser_change_pwd_btn').on('click', function(e){
		
	 	$.ajax( {type: "POST", url: $(this).data('path'), data: JSON.stringify({ 
	 		new_password: $('input#new-password').val(),
	 		})} )
	    .done(function( response ) {   
	    		    	
	    	if ( response.success ) {
	    		
	    		$("#change_pwd_dialog").removeClass("overlay-open");
	    		$('input#new-password').val("");
	    	}
	    	
	     	new PNotify({
	            title: response.flash.title,
	            text: response.flash.message,
	            type: response.flash.type
	 	    });
	    });
	 
	});
	
	$("input[type='text']").click(function () {
	   $(this).select();
	});
	
    $('#generate-api_pwd').pGenerator({
		  'bind': 'click',
		  'passwordElement': '',
		  'displayElement': '#new-password',
		  'passwordLength': 24,
		  'uppercase': true,
		  'lowercase': true,
		  'numbers':   true,
		  'specialChars': false,
		  'onPasswordGenerated': function(generatedPassword) {
			  $('#new-password').focus();
		   }
	  });
	  
	   $(".copy").click(function(){
		   var data_id = $(this).data('id');
		   $('#'+data_id).select();
		   var label = $("label[for='"+data_id+"']").text();
		   copyToClipboard(document.getElementById(data_id));
       }); 
});

function copyToClipboard(elem) {
	  // create hidden text element, if it doesn't already exist
    var targetId = "_hiddenCopyText_";
    var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
    var origSelectionStart, origSelectionEnd;
    if (isInput) {
        // can just use the original source element for the selection and copy
        target = elem;
        origSelectionStart = elem.selectionStart;
        origSelectionEnd = elem.selectionEnd;
    } else {
        // must use a temporary form element for the selection and copy
        target = document.getElementById(targetId);
        if (!target) {
            var target = document.createElement("textarea");
            target.style.position = "absolute";
            target.style.left = "-9999px";
            target.style.top = "0";
            target.id = targetId;
            document.body.appendChild(target);
        }
        target.textContent = elem.textContent;
    }
    // select the content
    var currentFocus = document.activeElement;
    target.focus();
    target.setSelectionRange(0, target.value.length);
    
    // copy the selection
    var succeed;
    try {
    	  succeed = document.execCommand("copy");
    } catch(e) {
        succeed = false;
    }
    // restore original focus
    if (currentFocus && typeof currentFocus.focus === "function") {
        currentFocus.focus();
    }
    
    if (isInput) {
        // restore prior selection
        elem.setSelectionRange(origSelectionStart, origSelectionEnd);
    } else {
        // clear temporary content
        target.textContent = "";
    }
    return succeed;
}