define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'eghlpayment',
                component: 'Eghl_PaymentMethod/js/view/payment/method-renderer/eghlpayment'
            }
        );
        return Component.extend({});
    }
);