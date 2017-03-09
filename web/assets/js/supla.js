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

function switchToLanguage(lng) {

    $.ajax({type: "POST", url: '/ajax/lngset/' + lng})
        .done(function (response) {
            fadeToUrl(window.location.pathname + window.location.search);
        });

}

function getDetailId() {
    if ($('#accessid-detail').length != 0)
        return '#accessid-detail';
    else if ($('#location-detail').length != 0)
        return '#location-detail';
    else if ($('#iodevice-detail').length != 0)
        return '#iodevice-detail';

    return '#unknown'
}

function fadeToUrl(url) {
    $('#block').fadeOut(300, function () {
        window.location = url;
    })
    return false;
}

function checkChannelState(state_class, value_id, ajax_action, timeout) {

    var channel_state = $("." + state_class);
    var exists = false;

    $(document).find("." + state_class).each(function (index, element) {
        $(this).find('#' + value_id).html('<div class="ajax_loader3"></div>');

        exists = true;
    });

    if (exists == false)
        return;

    var check_action = function () {

        $.ajax({type: "POST", url: '/ajax/' + ajax_action + '/' + channel_state.data('iodevid') + '/' + channel_state.data('id')})
            .done(function (response) {

                if (response.success == true) {

                    $(document).find("." + state_class).each(function (index, element) {
                        $(this).find('#' + value_id).html(response.value);
                    });

                    setTimeout(check_action, timeout);
                }
            });

    }

    check_action();
}

function htmlConnectionState(state, txt) {

    var c = state == 1 ? 'connected' : 'disconnected'

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

    }

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

(function ($) {

    var openSubChannelSelectClickHandler = function (e) {

        var csel = $("#channel_function_select");
        var osel = $("#subchannel_select");

        if (csel.length == 0 || osel.length == 0) return;

        var scname = osel.find('#subchannel_name');
        scname.text($(this).text());

        $('#' + csel.data('prefix') + osel.data('param_id')).val($(this).data('id'));
    }

    var relayTimeClickHandler = function (e) {

        var csel = $("#channel_function_select");
        var cfpt = $("#cfp_relay_time");

        if (csel.length == 0 || cfpt.length == 0) return;

        cfpt.find('button#relay_time_select').each(function (index, element) {
            $(this).removeClass('btn-success');
            $(this).removeClass('btn-default');
            $(this).addClass('btn-default');
        });

        $(this).addClass('btn-success');

        $('#' + csel.data('prefix') + cfpt.data('paramname')).val($(this).data('val'));

    }

    var channelFunctionAssignHandlers = function () {
        $('#subchannel_select a').on('click', openSubChannelSelectClickHandler);
        $('button#relay_time_select').on('click', relayTimeClickHandler);
    }

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

        $('SELECT#lngSelector').on('change', function (e) {
            switchToLanguage($(this).val());
        });

        $('.lng_switch').on('click', function (e) {
            switchToLanguage($(this).data('lng'));
        });

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
            $('#forgot-password-step1').fadeOut()

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

        $('#channel_function_select a').on('click', function (e) {

            var csel = $("#channel_function_select");
            var cparam = $("#channel_function_params");
            var pictogram = $("#pictogram_group");
            var pictogram_nofunc = $("#pictogram_no-function");
            var pictogram_temp = $("#pictogram_temperature");
            var pictogram_humidity = $("#pictogram_humidity");
            var pictogram_temphumidity = $("#pictogram_temphumidity");
            var pictogram_std = $("#pictogram_standard");
            var pictogram_depth = $("#pictogram_depth_sensor");
            var pictogram_distance = $("#pictogram_distance_sensor");

            var form = csel.closest("form");

            if (form.length == 0) return;

            form.find('button[type="submit"]').each(function (index, element) {
                $(this).addClass("disable")
            });

            csel.addClass("disable");
            cparam.addClass("disable");

            pictogram.fadeOut();

            $('#' + csel.data('prefix') + 'function').val(0);
            $('#' + csel.data('prefix') + 'param1').val(0);
            $('#' + csel.data('prefix') + 'param2').val(0);
            $('#' + csel.data('prefix') + 'param3').val(0);

            var fname = csel.find('#function_name');
            fname.text($(this).text());

            var function_id = $(this).data('id');

            $.ajax({type: "POST", url: '/iodev/ajax/getfuncparams/' + csel.data('channel_id') + '/' + function_id})
                .done(function (response) {

                    if (response.success == true) {

                        cparam.replaceWith(response.html);
                        cparam = $("#channel_function_params");

                        if (cparam.length != 0) {

                            var dparam1 = cparam.data('default-param1');
                            var dparam2 = cparam.data('default-param2');
                            var dparam3 = cparam.data('default-param3');

                            if (dparam1 !== undefined)
                                $('#' + csel.data('prefix') + 'param1').val(dparam1);

                            if (dparam2 !== undefined)
                                $('#' + csel.data('prefix') + 'param2').val(dparam2);

                            if (dparam3 !== undefined)
                                $('#' + csel.data('prefix') + 'param3').val(dparam3);

                        }

                        pictogram_temp.hide();
                        pictogram_humidity.hide();
                        pictogram_temphumidity.hide();
                        pictogram_std.hide();
                        pictogram_nofunc.hide();
                        pictogram_depth.hide();
                        pictogram_distance.hide();

                        if (function_id == 0) {
                            pictogram_nofunc.show();
                        } else if (function_id == 40) {
                            pictogram_temp.show();
                        } else if (function_id == 42) {
                            pictogram_humidity.show();
                        } else if (function_id == 45) {
                            pictogram_temphumidity.show();
                        } else if (function_id == 210) {
                            pictogram_depth.show();
                        } else if (function_id == 220) {
                            pictogram_distance.show();
                        } else {

                            var img = pictogram_std.find('img');

                            if (img != undefined && img.length != 0) {
                                img.attr('src', img.attr('src').replace(/\/\d+\.svg$/, "/" + function_id + ".svg"));
                                pictogram_std.show();
                            }

                        }

                        $('#' + csel.data('prefix') + 'function').val(function_id);
                        pictogram.hide();
                        pictogram.fadeIn();
                    }

                    csel.removeClass("disable");
                    cparam.removeClass("disable");
                    form.find('button[type="submit"]').each(function (index, element) {
                        $(this).removeClass("disable")
                    });

                    channelFunctionAssignHandlers();

                });

        });

        $('.access_id_list a').on('click', carouselItemClick('aid'));
        $('.location_list a').on('click', carouselItemClick('loc'));

        $('#logout').turnOffTV();

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

        $('.nav-link').click(function () {
            fadeToUrl($(this).attr('href'));
            return false;
        });

        /*
         $('.cd-btn').on('click', function(event){
         // Scrool fix for Google Chrome
         setTimeout(
         function() {
         var h = $('.cd-panel-content').height();
         $('.cd-panel-content').height(h);
         }, 500);

         });
         */

        carouselInit();

        channelFunctionAssignHandlers();
        checkChannelState('sensor_state', 'sensor_state_value', 'serverctrl-sensorstate', 5000);
        checkChannelState('thermometer_state', 'temperature_value', 'serverctrl-tempval', 10000);
        checkChannelState('humidity_state', 'humidity_value', 'serverctrl-humidityval', 10000);
        checkChannelState('depth_state', 'depth_value', 'serverctrl-distanceval', 5000);
        checkChannelState('distance_state', 'distance_value', 'serverctrl-distanceval', 5000);
        checkConnectionState(5000);

    });
})(jQuery);
