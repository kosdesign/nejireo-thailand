<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bss\QuoteExtension\Block\QuoteExtension\Item;

/**
 * Quote Item Configure block
 * Updates templates and blocks to show 'Update Cart' button and set right form submit url
 */
class Configure extends \Magento\Framework\View\Element\Template
{
    /**
     * Configure product view blocks
     * @return \Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        // Set custom submit url route for form - to submit updated options to cart
        $block = $this->getLayout()->getBlock('product.info');
        if ($block) {
            $itemId = (int) $this->getRequest()->getParam('id');
            if ($itemId) {
                $block->setSubmitRouteData(
                    [
                        'route' => 'quoteextension/quote/updateItemOptions',
                        'params' => ['id' => $itemId],
                    ]
                );
            }
        }

        return parent::_prepareLayout();
    }
}
