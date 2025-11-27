define([
    'jquery',
    "Plumrocket_ProductFilter/js/model/url",
    'Plumrocket_ProductFilter/js/model/prproductfilter',
    'Plumrocket_ProductFilter/js/product/list/toolbar',
    'Plumrocket_ProductFilter/js/model/swatch',
    'Plumrocket_ProductFilter/js/model/price'
], function ($, url, processor, toolbar, swatch, price) {
    'use strict';
    return function (widget) {
        $.widget('plumrocket.productfilter', widget, {
            _create: function () {
                var target = this;
                if (this.options.filterItemSelector) {
                    var self = this;

                    //Set pareameters for other js
                    price.auto = this.options.auto;
                    price.init = this;
                    price.urlModel = url;

                    url.separator = this.options.filterParamSeparator;
                    url.isSeoFriendly = this.options.isSeoFriendly;
                    url.categoryUrlSufix = this.options.categoryUrlSufix;
                    url.currentUrl = this.options._currentUrl;

                    //Rewrite toolbar function for getting url for toolbar item
                    toolbar.rewrite();

                    swatch.isSeoFriendly = this.options.isSeoFriendly;

                    //Emulate seleted parametes for swatches
                    swatch.emulateSelected(this.options.realParams);

                    processor.init(this);
                    if (this.options.auto) {
                        $(this.options.filterItemSelector).on('click',$.proxy(this._run, this));
                    } else {
                        $(this.options.filterItemSelector).on('click',$.proxy(this._add, this));
                    }

                    $(this.options.actionsSelector).on('click',$.proxy(this._action, this));
                    $(this.options.clearButton).on('click',$.proxy(this._clearAction, this));
                    $(this.options.removeFilterLink).on('click',$.proxy(this._removeItemAction, this));

                    $('#narrow-by-list .item a[data-request="price"]').on('click', $.proxy(price.changeRange, this));


                    var containerCollapsible = $(self.options.filterSelector).find('[data-role="collapsible"]');

                    if ($(window).width() <= 750 ) {
                        try {
                            containerCollapsible.collapsible('deactivate');
                        } catch(e) {}
                    }

                    if ($('body').hasClass('ppf-pos-toolbar')) {
                        $(document).mouseup(function (e)
                        {
                            if (!containerCollapsible.is(e.target) // if the target of the click isn't the container...
                                && containerCollapsible.has(e.target).length === 0) // ... nor a descendant of the container
                            {
                                try {
                                    containerCollapsible.collapsible('deactivate');
                                } catch (e) {}
                            }
                        });
                    }
                    $(this.options.filterItemSelector).each(function () {
                        if($(this).hasClass('selected')) {
                            target.addSelectedHtml($(this));
                        }
                    });

                    this.showFilterButton();
                }
            },
            _add: function (event)
            {
                var $item = $(event.currentTarget),
                    canRemove = $item.hasClass('selected');

                if ($item.data('radio') == true) {
                    $item.parents('.filter-options-content').find('.item a').removeClass('selected');
                    this.removeSelected($item.data('request'));
                }

                if (swatch.isSwatch($item)) {
                    var res = swatch.addSelected($item);
                    var request = swatch.getItemRequest($item);

                    request.value = url.convertValue(request.value);

                    if (res) {
                        this.addSelected(request.var, request.value);
                        this.addSelectedHtml($item);
                    } else {
                        this.removeSelected(request.var, request.value)
                    }
                } else {
                    if (canRemove) {
                        $item.removeClass('selected')
                        this.removeSelected($item.data('request'), $item.data('value'));
                        this.removeSelectedHtml($item.data('request')+$item.data('value'))
                    } else {
                        $item.addClass('selected')
                        this.addSelected($item.data('request'), $item.data('value'));
                        this.addSelectedHtml($item);
                    }
                }

                return false;
            },
            showFilterButton: function () {
                $(this.options.filterButton).show();
                $(this.options.filterButton).on('click', $.proxy(this._manualFilter, this));
            },
            addSelectedHtml: function ($item) {
                var target = this;
                var text = $item.find('span').html();
                var data_attribute = $item.data('request') + $item.data('value');
                var html = '<span class="action-multiselect-crumb" data-remove="'+data_attribute+'">' + text + '<button data-request="'+$item.data('request')+'" data-value="'+$item.data('value')+'" data-remove="'+data_attribute+'"  class="action-close-attr action-close" type="button"><span class="action-close-text">Close</span></button></span>';
                var elment = '.'+$item.data('request');
                $(elment).append(html);
                $(elment).find('.text').hide();
                $('.action-close-attr').on('click',function (e) {
                    e.preventDefault();
                    target.removeSelected($(this).data('request'), $(this).data('value'));
                    $('a[data-value="'+ $(this).data('value')+'"]').removeClass('selected');
                    target.removeSelectedHtml($(this).data('remove'));
                });
            },
            removeSelectedHtml: function (classRemove) {
                $('span[data-remove="'+classRemove+'"]').remove();
                $('.filter-options-title').each(function() {
                    ($(this).has('.action-multiselect-crumb').length ? $(this).find('.text').hide() : $(this).find('.text').show());
                });
            }   
        });

        return $.plumrocket.productfilter;
    }
});
