/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'slick',
    'rtResponsiveTables',
    'matchHeight'
], function (
    $,
    slick,
    config,
    element,
    rtResponsiveTables,
    matchHeight
) {
    'use strict';

    $.widget('mage.componentGlobal', {

        /**
         *
         * @private
         */
        _create: function () {
            self = this;
            self._scrollto();
            self._responsiveTables();
            setTimeout(function(){
                self._recentlyViewedSlider();
            }, 3000);

            //Set Match Height Item Equal
            $('.lib__mh-items .mh-item').matchHeight({ property: 'min-height' });
        },

        _scrollto: function () {
            $('a[href^="#link-"]').click(function () {
                var t = $(this).attr("href");
                var id = t.replace("link-", "");
                if ($(id).length) return $("html,body").animate({
                    scrollTop: $(id).offset().top
                }, 500), !1
            })
        },

        _responsiveTables: function () {
            setTimeout(function(){
                $("#product-table-options table").rtResponsiveTables({
                    containerBreakPoint: 941
                });
            }, 3000);

            $("#shopping-cart-table").css('display','block');
            $("#shopping-cart-table").rtResponsiveTables({
                containerBreakPoint: 720
            });
        },

        _recentlyViewedSlider: function () {
            if ($('body').children('#slider-wrap-recently')) {
                var sliderContainer = $('.products-recently-viewed');
                var slider = sliderContainer.find('#slider-wrap-recently');
                var item = slider.find('.product-item');
                sliderContainer.show();
                if (item.length > 2 && slider) {
                    slider.slick({
                        dots: true,
                        infinite: true,
                        speed: 1200,
                        slidesToShow: 5,
                        slidesToScroll: 4,
                        responsive: [{
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 4,
                                slidesToScroll: 3,
                            }
                        },
                            {
                                breakpoint: 768,
                                settings: {
                                    slidesToShow: 3,
                                    slidesToScroll: 2,
                                    arrows: false,
                                }
                            },
                            {
                                breakpoint: 576,
                                settings: {
                                    slidesToShow: 2,
                                    slidesToScroll: 2,
                                    arrows: false,
                                }
                            },
                        ]
                    });
                }
            }
        }
    });

    return $.mage.componentGlobal;
});