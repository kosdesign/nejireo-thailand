/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/ui-select',
    'jquery'
], function (Abstract, $) {
    'use strict';

    return Abstract.extend({
        updateOptions: function(options) {
            this.options(options);
        },

        setFilterValues: function(data)
        {
            this.value(data);
        },

        removeSelected: function (value, data, event) {
            event ? event.stopPropagation() : false;
            this.value.remove(value);
            $('[data-action="grid-filter-apply"]').trigger('click');
        },

        toggleOptionSelected: function (data) {
            var isSelected = this.isSelected(data.value);

            if (this.lastSelectable && data.hasOwnProperty(this.separator)) {
                return this;
            }

            if (!this.multiple) {
                if (!isSelected) {
                    this.value(data.value);
                }
                this.listVisible(false);
            } else {
                if (!isSelected) { /*eslint no-lonely-if: 0*/
                    this.value.push(data.value);
                } else {
                    this.value(_.without(this.value(), data.value));
                }
            }
            $('[data-action="grid-filter-apply"]').trigger('click');
            return this;
        }
    });
});
