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
namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Helper\Customer\AutoLogging;
use Bss\QuoteExtension\Helper\Data;
use Bss\QuoteExtension\Model\ManageQuoteFactory;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory as QuoteExtensionCollection;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Html\Links;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteFactory;

/**
 * Class View
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class View extends Action
{
    /**
     * @var QuoteExtensionCollection
     */
    protected $quoteExtensionCollection;
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var QuoteFactory
     */
    protected $mageQuoteFactory;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var AutoLogging
     */
    protected $bssHelperLogging;

    /**
     * View constructor.
     * @param QuoteExtensionCollection $quoteExtensionCollection
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param ManageQuoteFactory $quoteFactory
     * @param QuoteFactory $mageQuoteFactory
     * @param Data $helper
     * @param CheckoutSession $checkoutSession
     * @param AutoLogging $bssHelperLogging
     */
    public function __construct(
        QuoteExtensionCollection $quoteExtensionCollection,
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        ManageQuoteFactory $quoteFactory,
        QuoteFactory $mageQuoteFactory,
        Data $helper,
        CheckoutSession $checkoutSession,
        AutoLogging $bssHelperLogging
    ) {
        $this->quoteExtensionCollection = $quoteExtensionCollection;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->mageQuoteFactory = $mageQuoteFactory;
        $this->quoteFactory = $quoteFactory;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->bssHelperLogging = $bssHelperLogging;
    }

    /**
     * Dispatch Controller
     *
     * @param RequestInterface $request
     * @return ResponseInterface|Redirect
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->bssHelperLogging->isCustomerLoggedIn()) {
            $params = $this->_request->getParams();
            $isAutoLogging = $this->bssHelperLogging->isAutoLogging();
            if (isset($params['quote_id']) && $isAutoLogging && isset($params['token'])) {
                $requestQuote = $this->quoteFactory->create()->load($params['quote_id']);
                $token = $requestQuote->getToken();
                if ($requestQuote->getEntityId() && $token == $params['token']) {
                    $quote = $this->mageQuoteFactory->create()->load($requestQuote->getQuoteId());
                    $this->bssHelperLogging->setCustomerDataLoggin($quote);
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('quoteextension/quote/view/quote_id/' . $params['quote_id']);
                } else {
                    $this->_actionFlag->set('', 'no-dispatch', true);
                }
            } else {
                $this->_actionFlag->set('', 'no-dispatch', true);
            }
        }
        return parent::dispatch($request);
    }

    /**
     * Quote View Page
     *
     * @return Redirect|ResultInterface|Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $enable = $this->helper->isEnable();
        $quoteId = $this->getRequest()->getParam('quote_id');
        $quote = $this->quoteExtensionCollection->create()
            ->addFieldToFilter('main_table.entity_id', $quoteId)->getLastItem();
        $subUserId = $quote->getSubUserId();
        $this->coreRegistry->register('sub_user_id_quote', $subUserId);
        $mageQuote = $this->mageQuoteFactory->create()->load($quote->getQuoteId());
        if (!$this->checkPermissionSubUser($subUserId) || !$this->checkCustomerViewQuote($quote->getCustomerId())) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('You have no permission to see that quote.'));
            return $resultRedirect->setPath('quoteextension/quote/history');
        }
        if ($enable && $quoteId && $quote->getEntityId() && $mageQuote->getId()) {
            $resultPage = $this->resultPageFactory->create();
            $this->coreRegistry->register('current_quote_extension', $quote);
            $this->coreRegistry->register('current_quote', $mageQuote);
            $resultPage->getConfig()->getTitle()->set(__('Quote # %1', $quote->getIncrementId()));

            /** @var Links $navigationBlock */
            $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('quoteextension/quote/history');
            }

            $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
            if ($block) {
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
            $this->checkoutSession->setIsQuoteExtension($mageQuote->getId());
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('The request quote id no longer exists.'));
            return $resultRedirect->setPath('quoteextension/quote/history');
        }
    }

    /**
     * Check permission sub user with quote id
     *
     * @param string $subUserIdQuote
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkPermissionSubUser($subUserIdQuote) {
        if($this->helper->isEnableCompanyAccount()) {
            $subUserIdCurrent = $this->getRequest()->getParam("sub_user_id_current");
            $viewAllQuotes = $this->getRequest()->getParam("allow_view_all_quotes");
            if ($subUserIdCurrent && !$viewAllQuotes && $subUserIdQuote != $subUserIdCurrent) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check customer view quote id
     *
     * @param string $customerIdQuote
     * @return bool
     */
    public function checkCustomerViewQuote($customerIdQuote) {
        $customerIdCurrent = $this->helper->getCustomerIdCurrent();
        if($customerIdCurrent != $customerIdQuote) {
            return false;
        }
        return true;
    }
}
