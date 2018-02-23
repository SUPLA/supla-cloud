/*
 cloud::supla.js

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

    if (itempath == undefined
        || itempath.length == 0)
        return 'unknown';

    return itempath.replace(/\/view.*$/, "/ajax/" + fnc);
}

function ajaxPwdGen(len) {

    var btn = $("#form_pwd_gen_btn");
    var field = $("#form_password");

    if (btn.length && field.length) {

        field.after('<div class="ajax_loader1"></div>');
        btn.attr('disabled', true);

        $.ajax({type: "POST", url: '/ajax/pwdgen/' + len})
            .done(function (response) {

                btn.attr('disabled', false);
                $(".ajax_loader1").remove();

                if (response.success) {
                    field.attr('value', response.pwd);
                }

            });

    }

}

function getDetailId() {
    if ($('#accessid-detail').length != 0)
        return '#accessid-detail';
    else if ($('#location-detail').length != 0)
        return '#location-detail';
    else if ($('#iodevice-detail').length != 0)
        return '#iodevice-detail';
    else if ($('#api-detail').length != 0)
        return '#api-detail';
    
    return '#unknown'
}

function htmlConnectionState(state, txt) {

    var c = state == 1 ? 'connected' : 'disconnected';

    return '<span class="' + c + '">' + txt + '</span>';
}

function checkConnectionState(timeout) {

    var devs = [];
    var devid;

    $(document).find('.iodev_connection_state').each(function (index, element) {

        devid = $(this).data('id');

        if ($.inArray(devid, devs) == -1) {
            devs.push(devid);
        }

    });

    if (devs.length == 0) return;

    var check_action = function () {

        $.ajax({type: "POST", url: '/ajax/serverctrl-connstate', data: JSON.stringify({devids: devs})})
            .done(function (response) {

                if (response.success) {

                    $(document).find('.iodev_connection_state').each(function (index, element) {

                        devid = $(this).data('id');

                        if (response.states[devid] != undefined) {
                            $(this).html(htmlConnectionState(response.states[devid].state, response.states[devid].txt));

                            var dot1 = $(".dot1");
                            if (dot1 != undefined && dot1.length != 0 && dot1.data('id') == devid) {

                                if (response.states[devid].state == 1) {
                                    dot1.removeClass("red");
                                } else {
                                    dot1.addClass("red");
                                }
                            }
                            var devitem = $("#deviceitem");
                            if (devitem != undefined && devitem.length != 0 && devitem.data('id') == devid) {

                                if (response.states[devid].state == 1) {
                                    devitem.removeClass("disconnected");
                                    devitem.addClass("connected");
                                } else {
                                    devitem.removeClass("connected");
                                    devitem.addClass("disconnected");
                                }
                            }
                        }

                    });

                    setTimeout(check_action, timeout);
                }
            })

    };

    check_action();

}

function carouselInit() {

    if ($('.scroll_list_wrapper').length > 0) {

        var sl = $('.scroll_list');

        sl.owlCarousel({
            itemsCustom: [
                [0, 3],
                [500, 3],
                [700, 4],
                [900, 4],
                [1100, 4],
                [1300, 5],
                [1500, 6],
            ],
            rewindNav: true,
            rewindSpeed: 1000,
            navSpeed: 800,
            navigation: true,
            navigationText: ["<i class=\"pe-7s-angle-left\"></i>", "<i class=\"pe-7s-angle-right\"></i>"],
            scrollPerPage: true,
            pagination: true,
        });

        sl.liveFilter('#livefilter-input', 'li', {
            filterChildSelector: 'a'
        });

        $("#livefilter-input").focus(function () {
            sl.trigger('owl.goTo', 0)
        });

        var detail = $(getDetailId());
        if (detail.length > 0) {

            var id = detail.data('id');

            if (id != undefined && id != 0) {
                var n = 0;
                $(document).find('.scroll_list_wrapper a').each(function (index, element) {
                    if ($(this).data('id') == id) {
                        var owl = sl.data('owlCarousel');
                        owl.goTo(n);
                        return true;
                    }
                    n++;
                });

            }
        }

    }

}

/**
 * detect IE
 * returns version of IE or false, if browser is not Internet Explorer
 * http://stackoverflow.com/questions/19999388/check-if-user-is-using-ie-with-jquery
 */
function detectIE() {
    var ua = window.navigator.userAgent;

    var msie = ua.indexOf('MSIE ');
    if (msie > 0) {
        // IE 10 or older => return version number
        return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
    }

    var trident = ua.indexOf('Trident/');
    if (trident > 0) {
        // IE 11 => return version number
        var rv = ua.indexOf('rv:');
        return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
    }

    var edge = ua.indexOf('Edge/');
    if (edge > 0) {
        // IE 12 (aka Edge) => return version number
        return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
    }

    // other browser
    return false;
}

function newIconSelected() {

		var n = $(this).data('number');
		$('.overlay-changeicon').data('selected', n);
		$(this).find(':checkbox').prop('checked', true);
			
		$(this).parent('.row').parent('.channel-icons').find('.row').each(function () {
			
			$(this).find('label').each(function () { 
				if ( $(this).data('number') != n ) {
					$(this).find(':checkbox').prop('checked', false);
				}
			});
			
		});
		
}

function changeIconClick() {
	
	var sel = $('#channel_function_select');
	
	var datafield = sel.data('prefix') + 'altIcon';
	var functionfield = sel.data('prefix') + 'function';
	var alticon_max = sel.data('alticonmax');
	
	var val = $('#'+datafield).val();
	var fnc = $('#'+functionfield).val();
	
	var overlay = $('.overlay-changeicon .overlay');
	var rows = $('.overlay-changeicon #channel-icons');
	
	var row = '<div class="row">';
	var fn_suffix = '';
	
	for(a=0;a<=alticon_max;a++) {
		
		if ( a > 0 ) {
			
			if (a % 3 == 0 ) {
				row += '</div><div class="row">';
            }
            fn_suffix = '_' + a;
		}

		
		row+='<label class="col-xs-4" data-number="'+a+'"><input type="checkbox" name="icon['+a+']" '+ (val==a ? 'checked' : '') +'>';
		row+='<div class="item enabled "><img src="/assets/img/functions/'+fnc+fn_suffix+'.svg"></div>';
		row+='</label>';
	}
	
	row+='</div>';
	
	rows.html(row);
	$('.channel .channel-change-icon label').on('click', newIconSelected);
	
	overlay.addClass('overlay-open');
}

(function ($) {

    var carouselItemClick = function (itemType) {
        return function (e) {

            var id = $(this).data('id');
            var details = $('#details');

            if (id == undefined || id == 0 || details.length == 0)
                return;

            $(document).find('.scroll_list_wrapper a').each(function (index, element) {
                if ($(this).data('id') == id) {
                    $(this).addClass('selected');
                } else {
                    $(this).removeClass('selected');
                }
            });

            details.html('<div class="gmb-loader"><div></div><div></div><div></div></div>');

            $.ajax({type: "POST", url: FRONTEND_CONFIG.baseUrl + '/' + itemType + '/' + id + '/ajax/getdetails'})
                .done(function (response) {

                    if (response.success) {

                        details.hide();
                        details.html(response.html);
                        details.fadeIn();

                        checkConnectionState(5000);

                    } else {
                        details.html('');
                        location.href = location.href;
                    }

                });
        }
    };

    $(document).ready(function () {

        if ($("body").hasClass("first-time")) {
            $("#hello").fadeIn(600).delay(300).fadeOut(600);
        }

        $('#change_pwd_btn').on('click', function (e) {

            $.ajax({
                type: "POST", url: getAjaxUrl($(this).data('path'), 'changepassword'), data: JSON.stringify({
                    old_password: $('input#old-password').val(),
                    new_password: $('input#new-password').val(),
                    confirm_password: $('input#confirm-password').val()
                })
            })
                .done(function (response) {

                    if (response.success) {

                        $("#change_pwd_dialog").removeClass("overlay-open");

                        $('input#old-password').val("");
                        $('input#new-password').val("");
                        $('input#confirm-password').val("");

                    }

                    new PNotify({
                        title: response.flash.title,
                        text: response.flash.message,
                        type: response.flash.type
                    });
                });

        });

        $('#forgotpwd_btn').on('click', function () {
            $('#forgot-password-step2').hide();
            $('#forgot-password-step1').show();
        });

        $('button#password_reset_btn').on('click', function (e) {

            var pl = $('#' + $(this).data('preloader'));

            pl.show();
            $(this).hide();
            $('#forgot-password-step1').fadeOut();

            $.ajax({
                type: "POST", url: $(this).data('path'), data: JSON.stringify({
                    email: $('input#password_reset_email').val(),
                })
            })
                .done(function (response) {

                    if (response.success) {
                        pl.hide();
                        $('#forgot-password-step2').fadeIn();
                        $(this).fadeOut();

                    }

                });

        });

        $('button[name="confirmBtn"]').on('click', function (e) {

            var dialog = $(this).data('dialogid');
            $("#" + dialog).modal("hide");
            e.stopPropagation();

            var url = $(this).data('desturl');

            if (typeof url == 'string' && url.length > 0) {
                location.href = url;
            }
        });

        $('.access_id_list a').on('click', carouselItemClick('aid'));
        $('.location_list a').on('click', carouselItemClick('loc'));

        //open the lateral panel
        $('.cd-btn').on('click', function (event) {
            event.preventDefault();
            $('.cd-panel').addClass('is-visible');
        });
        //clode the lateral panel
        $('.cd-panel').on('click', function (event) {
            if ($(event.target).is('.cd-panel') || $(event.target).is('.cd-panel-close')) {
                $('.cd-panel').removeClass('is-visible');
                event.preventDefault();
            }
        });

        carouselInit();

        checkConnectionState(5000);
    });
})(jQuery);
