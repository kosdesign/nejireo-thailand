<?php

namespace Kos\SalesPart\Helper\QuoteExtension;

use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory as QuoteExtensionCollection;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Bss\QuoteExtension\Helper\Data;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Message\ManagerInterface;
use Kos\SalesPart\Magento\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Bss\QuoteExtension\Helper\FormatDate;
use Bss\QuoteExtension\Helper\HidePriceEmail;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Mail extends \Bss\QuoteExtension\Helper\Mail
{
    const ATTACH_FILE_TYPE = 'application/pdf';
    const ATTACH_FILE_NAME = 'quote.pdf';

    protected $dir;
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $reader;

    public function __construct(
        QuoteExtensionCollection $quoteExtensionCollection,
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        Data $helper,
        LayoutInterface $layout,
        StateInterface $inlineTranslation,
        ManagerInterface $messageManager,
        TransportBuilder $transportBuilder,
        SenderResolverInterface $senderResolver,
        FormatDate $emailData,
        HidePriceEmail $hidePriceEmail,
        Filesystem $filesystem,
        DirectoryList $dir
    ) {
        parent::__construct(
            $quoteExtensionCollection,
            $context,
            $storeManagerInterface,
            $helper,
            $layout,
            $inlineTranslation,
            $messageManager,
            $transportBuilder,
            $senderResolver,
            $emailData,
            $hidePriceEmail
        );
        $this->reader = $filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
        $this->dir = $dir;
    }

    public function sendNotificationNewQuoteEmailForCustomer($quote, $requestQuote)
    {
        $templateName = $this->getEmailNewQuoteForCustomer();
        $senderEmail = $quote->getCustomerEmail();

        if ($requestQuote->getStatus() === Status::STATE_PENDING
            || $requestQuote->getStatus() === Status::STATE_CANCELED
            || $requestQuote->getStatus() === Status::STATE_REJECTED
        ) {
            foreach ($quote->getAllVisibleItems() as $item) {
                /* @var $item \Magento\Quote\Model\Quote\Item */
                $product = $item->getProduct();
                $item->setNeedCheckPrice(true);
                $item->setProduct($product);
                if ($item->getProductType() == 'configurable') {
                    $parentProductId = $item->getProductId();
                    $childProductSku = $item->getSku();
                    $canShowPrice = $this->hidePriceEmail->canShowPrice($parentProductId, $childProductSku);
                } else {
                    $canShowPrice = $this->hidePriceEmail->canShowPrice($item->getProductId(), false);
                }
                if (!$canShowPrice) {
                    $quote->setNeedHidePrice(true);
                    break;
                }
            }
        }
        if ($senderEmail) {
            $senderName  = $this->getEmailSenderName();
            $recipientEmail = $quote->getCustomerEmail();
            $variables      = [
                'increment_id' => $requestQuote->getIncrementId(),
                'quote'        => $quote
            ];
            $storeId     = $this->storeManager->getStore()->getId();
            $recipientEmail = $this->getRecipientsEmail($recipientEmail);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId,
                $requestQuote->getPdfFile()
            );
        }
    }

    /**
     * Send Notification Email
     *
     * @param string $templateName
     * @param string $senderName
     * @param string $senderEmail
     * @param string||array $recipientEmail
     * @param array $variables
     * @param int $storeId
     * @param string $fileAttachment
     * @return bool
     */
    protected function send(
        $templateName,
        $senderName,
        $senderEmail,
        $recipientEmail,
        $variables,
        $storeId,
        $fileAttachment = null
    ) {
        $this->inlineTranslation->suspend();
        try {
            $senderEmail = $this->getEmailSender();
            if (is_array($recipientEmail)) {
                foreach ($recipientEmail as $recipient) {
                    $this->_send(
                        $templateName,
                        $senderName,
                        $senderEmail,
                        $recipient,
                        $variables,
                        $storeId,
                        $fileAttachment
                    );
                }
            } else {
                $this->_send(
                    $templateName,
                    $senderName,
                    $senderEmail,
                    $recipientEmail,
                    $variables,
                    $storeId,
                    $fileAttachment
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage() . $recipientEmail);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->messageManager->addErrorMessage(__('We can\'t send the email quote right now.'));
        }

        $this->inlineTranslation->resume();
        return true;
    }

    /**
     * Send Notification Email
     *
     * @param string $templateName
     * @param string $senderName
     * @param string $senderEmail
     * @param string $recipientEmail
     * @param array $variables
     * @param int $storeId
     * @param string $fileAttachment
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _send(
        $templateName,
        $senderName,
        $senderEmail,
        $recipientEmail,
        $variables,
        $storeId,
        $fileAttachment = null
    ) {
        if ($fileAttachment) {
            $pdfPath = $this->dir->getPath('var') . '/' . trim($fileAttachment);
            $pdfContent = $this->reader->readFile($pdfPath);
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateName)
                ->setTemplateOptions([
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars($variables)
                ->setFrom([
                    'name'  => $senderName,
                    'email' => $senderEmail
                ])
                ->addTo($recipientEmail)
                ->setReplyTo($senderEmail)
                ->addAttachment($pdfContent, self::ATTACH_FILE_NAME, self::ATTACH_FILE_TYPE)
                ->getTransport();
        } else {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateName)
                ->setTemplateOptions([
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars($variables)
                ->setFrom([
                    'name'  => $senderName,
                    'email' => $senderEmail
                ])
                ->addTo($recipientEmail)
                ->setReplyTo($senderEmail)
                ->getTransport();
        }

        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
}