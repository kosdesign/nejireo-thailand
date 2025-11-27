define([
        'jquery',
        'mage/utils/wrapper',
        'Plumrocket_ProductFilter/js/model/swatch'
    ], function ($, wrapper, swatch) {
        'use strict';

        return function (prproductfilter) {
            /** Override default _affterAjax */
            prproductfilter._affterAjax = wrapper.wrapSuper(prproductfilter._affterAjax, function (data) {
                $('body').trigger('contentUpdated');
                $('.swatch-option-tooltip').hide();
                setTimeout(function () {
                    if (swatch && data.realParams) {
                        swatch.emulateSelected(data.realParams, true);
                    }
                }, 1000);

                setTimeout(function () {
                    $(document).scrollTop($("#layered-filter-block").offset().top);
                }, 1);

                var self = this;
                setTimeout(function () {
                    self._init._create();

                    //Fix for double addToCart
                    if ($.fn.catalogAddToCart) {
                        $("form[data-role='tocart-form']").each(function () {
                            var form = jQuery(this);
                            if (!$._data(form[0], 'events') || !$._data(form[0], 'events')['submit']) {
                                form.catalogAddToCart();
                            }
                        });
                    }
                }, 500);

                if (window.setGridItemsEqualHeight) {
                    setGridItemsEqualHeight($);
                }
            });
            return prproductfilter;
        };
    }
);
