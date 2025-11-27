/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define(
    [
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'jquery'
    ],
    function (url, fullScreenLoader, $) {
        'use strict';

        return {
            redirectUrl: window.checkoutConfig.defaultSuccessPageUrl,

            /**
             * Provide redirect to page
             */
            execute: function () {
                //fullScreenLoader.startLoader();
                $('#checkout-loader-spinner').show();
                window.location.replace(url.build(this.redirectUrl));
            }
        };
    }
);
