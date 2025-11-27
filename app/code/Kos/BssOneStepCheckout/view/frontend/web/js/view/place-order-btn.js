/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_OneStepCheckout
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'ko',
    'jquery',
    'Bss_OneStepCheckout/js/view/place-order-btn',
    'uiRegistry',
    'mage/translate',
    'mage/url'
], function (
    ko,
    $,
    Component,
    registry,
    $t,
    url
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Kos_BssOneStepCheckout/action/place-order-btn'
        },

        placeOrderLabel: ko.observable($t('Proceed to Checkout')),

        /** @inheritdoc */
        initialize: function () {
            this._super();
            var self = this;
            self.validationFieldScroll();
        },

        validationFieldScroll: function() {
            $(document).on('click','body .action.primary.btn-placeorder', function(){
                //Customer field
                if ($('.input-text.mage-error').length > 0) {
                    $('html, body').animate({
                        scrollTop: ($('.input-text.mage-error:first').offset().top - 100)
                    }, 500);
                } else {
                    if($('._required._error:visible:first').offset() !== undefined) {
                        $('html, body').animate({
                            scrollTop: $('._required._error:visible:first').offset().top
                        }, 500);
                    }
                }
            });
        },

        getCheckoutCartUrl: function() {
            return url.build('checkout/cart');;
        },
    });
});