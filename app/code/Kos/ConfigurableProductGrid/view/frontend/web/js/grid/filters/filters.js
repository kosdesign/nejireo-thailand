
define([
    'Magento_Ui/js/grid/filters/filters',
    'mageUtils'
], function (Abstract, utils) {
    'use strict';

    return Abstract.extend({
        /**
         * Sets filters data to the applied state.
         *
         * @returns {Filters} Chainable.
         */
        apply: function () {
            this.filters['auto_filter'] = 1;
            this._super();
        },

        /**
         * Clears filters data.
         *
         * @param {Object} [filter] - If provided, then only specified
         *      filter will be cleared. Otherwise, clears all data.
         * @returns {Filters} Chainable.
         */
        clear: function (filter) {
            // console.log(this.active);
            filter ?
                filter.clear() :
                _.invoke(this.active, 'clear');

            // this.filters = [];
            this.elems.filter(function (elem) {
                elem.value([]);
            });

            this.apply();

            return this;
        }
    });
});
