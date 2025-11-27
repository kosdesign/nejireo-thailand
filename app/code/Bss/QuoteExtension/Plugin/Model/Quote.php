<?php
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
 * @category   BSS
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Plugin\Model;

use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class Quote
 *
 * @package Bss\QuoteExtension\Plugin\Model
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Quote
{
    protected $quoteExtensionPlace;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * Constructs an object to override the cart ID parameter on a request.
     *
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\QuoteExtensionPlace $quoteExtensionPlace,
        CheckoutSession $checkoutSession
    ) {
        $this->quoteExtensionPlace = $quoteExtensionPlace;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Unset the unwanted shipping rates if the quotation shipping rate is selected
     *
     * @param \Bss\QuoteExtension\Model\Quote $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetIsActive($subject, $result)
    {
        if ($this->quoteExtensionPlace->checkQuoteExtensionPlace()) {
            return true;
        }
        return $result;
    }

    /**
     * @param $subject
     * @param $result
     * @param $quoteId
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoadByIdWithoutStore($subject, $result, $quoteId)
    {
        if ($result->getQuoteExtension() && !$result->getData('is_active')) {
            $result->setIsSuperMode(true);
        }
        return $result;
    }
}
