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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Block\Cart\Item\Renderer;

use Magento\Bundle\Helper\Catalog\Product\Configuration;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Class QuoteExtension
 *
 * @package Bss\QuoteExtension\Block
 */
class Bundle extends \Magento\Bundle\Block\Checkout\Cart\Item\Renderer
{
    /**
     * @var \Bss\QuoteExtension\Helper\Model
     */
    protected $helperModel;

    /**
     * @var \Bss\QuoteExtension\Model\ManageQuote|null
     */
    protected $quoteExtension = null;
    /**
     * @var \Bss\QuoteExtension\Model\ManageQuoteFactory
     */
    protected $quoteExtensionFactory;

    /**
     * Bundle constructor.
     * @param \Bss\QuoteExtension\Helper\Model $helperModel
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param InterpretationStrategyInterface $messageInterpretationStrategy
     * @param Configuration $bundleProductConfiguration
     * @param array $data
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\Model $helperModel,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
        Configuration $bundleProductConfiguration,
        array $data = []
    ) {
        $this->helperModel = $helperModel;
        parent::__construct(
            $context,
            $productConfig,
            $checkoutSession,
            $imageBuilder,
            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
            $bundleProductConfiguration,
            $data
        );
    }

    /**
     * Get quote extension
     *
     * @return \Bss\QuoteExtension\Model\ManageQuote|null
     */
    public function getQuoteExtension()
    {
        return $this->helperModel->getQuoteExtension();
    }
}
