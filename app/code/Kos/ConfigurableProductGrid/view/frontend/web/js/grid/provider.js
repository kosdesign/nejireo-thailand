/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/grid/provider',
    'uiRegistry'
], function (Element, registry) {
    'use strict';

    return Element.extend({
        /**
         * Overrides current data with a provided one.
         *
         * @param {Object} data - New data object.
         * @returns {Provider} Chainable.
         */
        setData: function (data) {
            data = this.processData(data);
            
            this.set('data', data);

            var plating = registry.get('kos_product_list_custom.kos_product_list_custom.listing_filters.plating');
            var diameter = registry.get('kos_product_list_custom.kos_product_list_custom.listing_filters.diameter');
            var material = registry.get('kos_product_list_custom.kos_product_list_custom.listing_filters.material');
            var length = registry.get('kos_product_list_custom.kos_product_list_custom.listing_filters.length');

            if ("filters" in data) {
                if(plating) {
                    var platingOptions = data.filters.plating;
                    plating.updateOptions(platingOptions);
                }
                
                if(diameter) {
                    var diameterOptions = data.filters.diameter;
                    diameter.updateOptions(diameterOptions);
                }
                
                if(material) {
                    var materialOptions = data.filters.material;
                    material.updateOptions(materialOptions);
                }

                if(length) {
                    var lengthOptions = data.filters.length;
                    length.updateOptions(lengthOptions);
                }
            }

            if(data.filterValue) {
                for (var key in data.filterValue) {
                    switch(key) {
                        case 'plating':
                            if(plating) {
                                plating.setFilterValues(data.filterValue[key]);
                            };
                            break;
                        case 'diameter':
                                if(diameter) {
                                    diameter.setFilterValues(data.filterValue[key]);
                                };
                                break;
                        case 'material':
                            if(material) {
                                material.setFilterValues(data.filterValue[key]);
                            };
                            break;
                        case 'length':
                            if(length) {
                                length.setFilterValues(data.filterValue[key]);
                            };
                            break;
                    }
                }
            }
            
            return this;
        }
    });
});
