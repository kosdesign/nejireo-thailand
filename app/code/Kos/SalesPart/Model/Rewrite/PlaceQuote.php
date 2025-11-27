<?php

namespace Kos\SalesPart\Model\Rewrite;

use Psr\Log\LoggerInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PlaceQuote extends \Bss\QuoteExtension\Model\PlaceQuote
{
    const PREFIX_QUOTE_NUMBER = 'M';
    protected $printHelper;
    protected $helperCustomQuote;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        LoggerInterface $logger,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote $expiredQuote,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Bss\QuoteExtension\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Bss\QuoteExtension\Helper\QuoteExtension\PrintHelper $printHelper,
        \Kos\CustomBssQuote\Helper\Data $helperCustomQuote
    ) {
        $this->printHelper = $printHelper;
        $this->helperCustomQuote = $helperCustomQuote;
        parent::__construct(
            $quoteRepository,
            $manageQuote,
            $logger,
            $sequenceManager,
            $expiredQuote,
            $customerRepository,
            $addressRepository,
            $helper,
            $checkoutSession
        );
    }

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
            foreach($quote->getAllVisibleItems() as $itemq){
                $itemQty = $itemq->getQty();
                $itemQty = $itemQty > 0 ? $itemQty : 1;
                $itemq->setDayToShip($this->helperCustomQuote->getDayToShip($itemQty,$itemq));
                $itemq->save();
            }
            $incrementId = self::PREFIX_QUOTE_NUMBER . $this->sequenceManager->getSequence(
                'quote_extension',
                $quote->getStoreId()
            )->getNextValue();

            $customer = $quote->getCustomer();
            $curentTime = $this->helper->getCurrentDateTime();
            $expiry = $this->expiredQuote->calculatorExpiredDay($curentTime);

            $pdfFileName = $this->generatePdfTemplate($quote->getId(), $incrementId);

            $data = [
                'quote_id'     => $quote->getId(),
                'increment_id' => $incrementId,
                'expiry'       => $expiry,
                'status'       => $this->helper->returnPendingStatus(),
                'email'        => $customer ? $quote->getCustomerEmail() : $customer->getEmail(),
                'customer_id'  => $customer ? $quote->getCustomerId() : '',
                'store_id'     => $quote->getStoreId(),
                'version'      => 0,
                'pdf_file'     => $pdfFileName
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

    protected function generatePdfTemplate($quoteId, $incrementId)
    {
        try {
            $quote = $this->printHelper->getQuoteRepository()->get($quoteId);
            $pdf = $this->printHelper->getPrintPdf()->getPdf(['quote' => [$quote], 'data' => ['incrementId' => $incrementId]]);
            $date = $this->printHelper->getDateTime()->date('Y-m-d_H-i-s');
            $fileContent = $pdf->render();
            $fileName = 'quote_kos_' . $date . '.pdf';
            $this->printHelper->getFileFactory()->create(
                $fileName,
                $fileContent,
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        } catch (\Exception $e) {
            $fileName  = 'Error!!';
        }
        return $fileName;
    }
}