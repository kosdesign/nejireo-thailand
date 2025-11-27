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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Controller\Index;

use Bss\QuoteExtension\Helper\Data as Config;
use Bss\QuoteExtension\Model\ManageQuote;
use Magento\Checkout\Controller\Onepage;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session as SessionModel;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\LayoutFactory as ResultLayout;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Bss\QuoteExtension\Model\Type\Onepage as ModelOnepage;

/**
 * Class Index
 *
 * @package Bss\QuoteExtension\Controller\Index
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends Onepage
{
    /**
     * Checkout helper
     *
     * @var Data
     */
    protected $checkoutHelper;

    /**
     * Checkout session
     *
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var ManageQuote
     */
    protected $manageQuote;

    /**
     * @var ModelOnepage
     */
    protected $modelOnepage;

    /**
     * Quote extension helper
     *
     * @var Config
     */
    protected $configHelper;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param Registry $coreRegistry
     * @param InlineInterface $translateInline
     * @param Validator $formKeyValidator
     * @param ScopeConfigInterface $scopeConfig
     * @param LayoutFactory $layoutFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param PageFactory $resultPageFactory
     * @param ResultLayout $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     * @param JsonFactory $resultJsonFactory
     * @param Data $checkoutHelper
     * @param SessionModel $checkoutSession
     * @param ManageQuote $manageQuote
     * @param ModelOnepage $modelOnepage
     * @param Config $configHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        Registry $coreRegistry,
        InlineInterface $translateInline,
        Validator $formKeyValidator,
        ScopeConfigInterface $scopeConfig,
        LayoutFactory $layoutFactory,
        CartRepositoryInterface $quoteRepository,
        PageFactory $resultPageFactory,
        ResultLayout $resultLayoutFactory,
        RawFactory $resultRawFactory,
        JsonFactory $resultJsonFactory,
        Data $checkoutHelper,
        SessionModel $checkoutSession,
        ManageQuote $manageQuote,
        ModelOnepage $modelOnepage,
        Config $configHelper
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->checkoutSession = $checkoutSession;
        $this->manageQuote = $manageQuote;
        $this->modelOnepage = $modelOnepage;
        $this->configHelper = $configHelper;
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory
        );
    }

    /**
     * Checkout page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->configHelper->isEnable()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/*/');
        }

        $dataPost = $this->getRequest()->getParams();
        if (isset($dataPost['quote']) && isset($dataPost['token'])) {
            $this->manageQuote->load($dataPost['quote']);
            $customerId = $this->_customerSession->isLoggedIn() ? $this->_customerSession->getCustomerId() : 0;
            if ($this->manageQuote->getEntityId() == $dataPost['quote']
                && $customerId == $this->manageQuote->getCustomerId()
                && $dataPost['token'] == $this->manageQuote->getToken()
            ) {
                $quote = $this->quoteRepository->get($this->manageQuote->getQuoteId());
                $quote->setIsSuperMode(true);
                $this->getOnepage()->setQuote($quote);
                if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
                    return $this->resultRedirectFactory->create()->setPath('quoteextension/quote');
                }

                if (!$this->_customerSession->isLoggedIn()) {
                    $this->messageManager->addErrorMessage(__('Guest checkout is disabled.'));
                    return $this->resultRedirectFactory->create()->setPath('quoteextension/quote');
                }

                $currentUrl = $this->_url->getUrl('*/*/*', ['_secure' => true]);
                $this->_customerSession->setBeforeAuthUrl($currentUrl);
                $this->_customerSession->regenerateId();
                $this->getOnepage()->initCheckout();
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__('Quote Checkout'));
                $resultPage->getLayout()
                    ->getBlock('checkout.root')
                    ->setQuoteExtension($quote)
                    ->setQuoteExtensionToken($dataPost['token'])
                    ->setManageQuoteExtension($this->manageQuote->getId());
                return $resultPage;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/*/');
    }

    /**
     * Get one page checkout model
     *
     * @return ModelOnepage
     * @codeCoverageIgnore
     */
    public function getOnepage()
    {
        return $this->modelOnepage;
    }
}
