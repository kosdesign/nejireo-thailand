define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    'use strict';

    return function (target) {
        return target.extend({
            /**
             * Extends Component object by storage observable messages.
             */
            initialize: function () {
                this._super();
                var self = this;
                self.messages.subscribe(function(messages) {
                    if (messages.messages) {
                        if (messages.messages.length > 0) {
                            setTimeout(function() {
                                customerData.set('messages', {});
                            }, 5000);
                        }
                    }
                });
            }
        });
    }
});