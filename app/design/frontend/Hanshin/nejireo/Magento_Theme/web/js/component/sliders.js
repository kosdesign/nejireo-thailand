/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery-ui-modules/widget',
    'mage/translate',
    'slick'
], function ($, slick, config) {
    'use strict';

    $.widget('mage.componentSliders', {

        /**
         *
         * @private
         */
        _create: function () {
            self = this;
            var elm         = this.options.elm;
            var countItems  = this.options.countItems;
            var dots        = this.options.dots;
            self._sliderSlick(countItems, elm, dots);
        },

        /**
         *
         * @param items
         * @param elm
         * @param dots
         * @private
         */
        _sliderSlick: function (items, elm, dots) {
            if(elm && items > 2) {
                var slider = $(elm);
                slider.slick({
                    dots: dots,
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
    });

    return $.mage.componentSliders;
});