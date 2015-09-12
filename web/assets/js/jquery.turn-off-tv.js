/*
 * jQuery turnOffTV
 * Version 1.0
 * https://github.com/iamvanja/turn-off-tv
 *
 * jQuery plugin creates a turn off animation of an old TV in the current browser viewport.
 *
 * Copyright (c) 2013 Vanja Gavric (vanja.gavric.org)
 * Dual licensed under the MIT and GPL licenses.
*/

;(function($) {
    'use strict';
    $.fn.turnOffTV = function( options ) {

        // Establish our default settings
        var $this = $(this),
            settings = $.extend({
                bodyWrapId  : 'body-wrap',
                tvBoxId     : 'tv-box',
                tvColor     : '#000',
                bodyBgColor : $('body').css('background-color'),
                href        : null
            }, options);

        if (typeof $this.attr('href') !== 'undefined') {
            settings.href = $this.attr('href');
        }

        $this.bind("click.turnOffTV", function (e) {
            e.preventDefault();
            var viewportHalfWidth = parseInt(document.documentElement.clientWidth/2, 10),
                viewportHalfHeight = parseInt(document.documentElement.clientHeight/2, 10),
                $tvBox = $('<div/>', {
                    id : settings.tvBoxId,
                    style: 'border-color:'+settings.tvColor
                }),
                $bodyWrap = $('<div/>', {
                    id: settings.bodyWrapId,
                    style: 'background:'+settings.bodyBgColor
                }),
                borderTopBottom = {
                    'border-top-width'      : viewportHalfHeight-1,
                    'border-bottom-width'   : viewportHalfHeight,
                    'opacity'               : 1
                },
                borderLeftRight = {
                    'border-left-width'     : viewportHalfWidth,
                    'border-right-width'    : viewportHalfWidth
                },
                callback = function(){
                    $(this)
                        .css({ 'background-color':'#fff' })
                        .animate(
                            borderLeftRight, 600, function(){

                                // animation complete
                                $(this).css({
                                    'background-color' : settings.tvColor
                                });
                                if (settings.href){
                                    window.location.href = settings.href;
                                }
                                return this;
                            }
                        );
                };

            // set body color, 
            // wrap all body elements, 
            // append tv box element
            $('body').css({'background-color' : settings.tvColor})
                     .wrapInner($bodyWrap)
                     .append($tvBox);

            // animate body squash
            // element caching ($bodyWrap) not possible (animation doesn't work on FX)
            // scaleY animation runs if the browser supports it
            $('#'+settings.bodyWrapId).animate({
                textIndent: 0.4
            },
            {
                step: function(now,fx) {
                    $(this).css('transform', 'scaleY('+ now +')');
                },
                duration:150
            }, 'linear');

            // animate old TV
            return $tvBox.animate(
                borderTopBottom, 500, function(){
                    // callback
                    callback.bind(this)();
                }
            );
        });
    };
}(jQuery));