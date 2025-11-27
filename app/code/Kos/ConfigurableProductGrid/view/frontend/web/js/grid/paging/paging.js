define([
    'jquery',
    'Magento_Ui/js/grid/paging/paging',
], function ($, Abstract) {
    'use strict';

    return Abstract.extend({
        /**
         * Sets cursor to the provied value.
         *
         * @param {(Number|String)} value - New value of the cursor.
         * @returns {Paging} Chainable.
         */
        setPage: function (value) {
            this.current = this.normalize(value);
            if($('#product-table-options tbody .data-row').length > 0) {
                $(document).scrollTop($("#product-table-options").offset().top);
            }
            return this;
        }
    });
});
