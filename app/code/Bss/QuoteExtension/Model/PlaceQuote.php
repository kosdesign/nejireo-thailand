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

namespace Bss\QuoteExtension\Model;

use Psr\Log\LoggerInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Bss\QuoteExtension\Api\PlaceQuoteInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\ShippingMethodInterface;

/**
 * Class PlaceQuote
 *
 * @package Bss\QuoteExtension\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PlaceQuote implements PlaceQuoteInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var ManageQuote
     */
    protected $manageQuote;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\SalesSequence\Model\Manager
     */
    protected $sequenceManager;
    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote
     */
    protected $expiredQuote;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * PlaceQuote constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param ManageQuote $manageQuote
     * @param LoggerInterface $logger
     * @param \Magento\SalesSequence\Model\Manager $sequenceManager
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote $expiredQuote
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        LoggerInterface $logger,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote $expiredQuote,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Bss\QuoteExtension\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->manageQuote = $manageQuote;
        $this->logger = $logger;
        $this->sequenceManager = $sequenceManager;
        $this->expiredQuote = $expiredQuote;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Set shipping information and place quote for a specified quote cart.
     * @param int $cartId
     * @param string $customerNote
     * @param ShippingMethodInterface $shippingMethod
     * @param AddressInterface $shippingAddress
     * @return int|void
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function saveShippingInformationAndPlaceQuote(
        $cartId,
        $customerNote,
        ShippingMethodInterface $shippingMethod,
        AddressInterface $shippingAddress
    ) {
        if ($this->helper->isRequiredAddress()) {
            if (!$shippingAddress->getCountryId()) {
                throw new StateException(__('The shipping address is missing. Set the address and try again.'));
            }
        }
        $ip = $this->expiredQuote->gretemoteAddress();
        $carrierCode = $shippingMethod->getCarrierCode();
        $methodCode = $shippingMethod->getMethodCode();
        $quote = $this->quoteRepository->get($cartId);
        $address = $shippingAddress->getData();
        try {
            $quote->setRemoteIp($ip);
            $quote->setCustomerNote($customerNote);
            if ($this->helper->isRequiredAddress()) {
                $quote->getShippingAddress()->addData($address)
                    ->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->setShippingMethod($carrierCode . '_' . $methodCode);
                $quote->collectTotals();
            }
            $quote->setIsActive(false);
            $this->quoteRepository->save($quote);
            $this->_prepareCustomerQuote($quote);
            $incrementId = $this->sequenceManager->getSequence(
                'quote_extension',
                $quote->getStoreId()
            )->getNextValue();

            $customer = $quote->getCustomer();
            $curentTime = $this->helper->getCurrentDateTime();
            $expiry = $this->expiredQuote->calculatorExpiredDay($curentTime);
            $data = [
                'quote_id'     => $quote->getId(),
                'increment_id' => $incrementId,
                'expiry'       => $expiry,
                'status'       => $this->helper->returnPendingStatus(),
                'email'        => $customer ? $quote->getCustomerEmail() : $customer->getEmail(),
                'customer_id'  => $customer ? $quote->getCustomerId() : '',
                'store_id'     => $quote->getStoreId(),
                'version'      => 0
            ];

            $this->manageQuote->setData($data);
            $this->manageQuote->save();
            $this->checkoutSession->setLastManaQuoteExtensionId($this->manageQuote->getEntityId());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(
                __('The quote cannot placed. Verify the input data and try again.')
            );
        }
        return $this->manageQuote->getEntityId();
    }

    /**
     * Validate quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @throws InputException
     * @throws NoSuchEntityException
     * @return void
     */
    protected function validateQuote(\Magento\Quote\Model\Quote $quote)
    {
        if (0 == $quote->getItemsCount()) {
            throw new InputException(
                __("The shipping method can't be set for an empty cart. Add an item to cart and try again.")
            );
        }
    }

    /**
     * Prepare quote for customer order submit
     *
     * @param Quote $quote
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareCustomerQuote($quote)
    {
        /** @var Quote $quote */
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $this->customerRepository->getById($quote->getCustomerId());
        $hasDefaultBilling = (bool)$customer->getDefaultBilling();
        $hasDefaultShipping = (bool)$customer->getDefaultShipping();

        if ($shipping && !$shipping->getSameAsBilling()
            && (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())
        ) {
            $shippingAddress = $shipping->exportCustomerAddress();
            if (!$hasDefaultShipping) {
                //Make provided address as default shipping address
                $shippingAddress->setIsDefaultShipping(true);
                $hasDefaultShipping = true;
                if (!$hasDefaultBilling && !$billing->getSaveInAddressBook()) {
                    $shippingAddress->setIsDefaultBilling(true);
                    $hasDefaultBilling = true;
                }
            }
            //save here new customer address
            $shippingAddress->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($shippingAddress);
            $quote->addCustomerAddress($shippingAddress);
            $shipping->setCustomerAddressData($shippingAddress);
            $this->addressesToSync[] = $shippingAddress->getId();
            $shipping->setCustomerAddressId($shippingAddress->getId());
        }

        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $billingAddress = $billing->exportCustomerAddress();
            if (!$hasDefaultBilling) {
                //Make provided address as default shipping address
                if (!$hasDefaultShipping) {
                    //Make provided address as default shipping address
                    $billingAddress->setIsDefaultShipping(true);
                }
                $billingAddress->setIsDefaultBilling(true);
            }
            $billingAddress->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($billingAddress);
            $quote->addCustomerAddress($billingAddress);
            $billing->setCustomerAddressData($billingAddress);
            $this->addressesToSync[] = $billingAddress->getId();
            $billing->setCustomerAddressId($billingAddress->getId());
        }
        if ($shipping && !$shipping->getCustomerId() && !$hasDefaultBilling) {
            $shipping->setIsDefaultBilling(true);
        }
    }
}
