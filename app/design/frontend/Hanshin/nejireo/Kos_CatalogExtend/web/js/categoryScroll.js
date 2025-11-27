define([
    'jquery',
    'mage/translate',
    'niceScroll',
    'jquery/ui'
], function($, $t) {
    "use strict";

    $.widget('kos.categoryScroll', {
        _create: function() {
            if($(window).width() > 1024){
                $(".cat-scroll").niceScroll({
                    cursorwidth: "7px",
                    cursorcolor: "#E6552F",
                    background: "#F0F0F3" // change css for rail background
                });


                $(".level0").hover(function () {
                    $(this).find('.scroll-0').niceScroll({
                        cursorwidth: "7px",
                        cursorcolor: "#E6552F",
                        background: "#F0F0F3" // change css for rail background
                    });
                });

                $(".level1").hover(function () {
                    $(this).find('.scroll-1').niceScroll({
                        cursorwidth: "7px",
                        cursorcolor: "#E6552F",
                        background: "#F0F0F3" // change css for rail background
                    });
                });
            }

            $( ".subcat-link-list .category-item" ).hover(function() {
                var imageUrl = $( this ).children('.child-image').data('image');
                var imageBanner = $(this).closest('.subcat-list').children('.subcat-image');
                imageBanner.find('.image').attr('style', 'background-image:url('+imageUrl+')');
            });
        }
    });

    return $.kos.categoryScroll;
});
