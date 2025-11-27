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

namespace Bss\QuoteExtension\Helper;

use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\ManageQuote;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory as QuoteExtensionCollection;
use Exception;
use IntlDateFormatter;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Mail
 *
 * @package Bss\QuoteExtension\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mail extends AbstractHelper
{
    const PATH_REQUEST4QUOTE_EMAIL_IDENTITY = 'bss_request4quote/request4quote_email_config/sender_email_identity';
    const PATH_REQUEST4QUOTE_EMAIL_COPY = 'bss_request4quote/request4quote_email_config/send_email_copy';
    const PATH_REQUEST4QUOTE_NEW_QUOTE = 'bss_request4quote/request4quote_email_config/new_quote_extension';
    const PATH_REQUEST4QUOTE_NEW_QUOTE_CUSTOMER = 'bss_request4quote/request4quote_email_config/new_quote_extension_customer';
    const PATH_REQUEST4QUOTE_RECEIVE_EMAIL = 'bss_request4quote/request4quote_email_config/receive_email_identity';
    const PATH_REQUEST4QUOTE_QUOTE_ACCEPT = 'bss_request4quote/request4quote_email_config/quote_extension_accept';
    const PATH_REQUEST4QUOTE_QUOTE_COMPLETE = 'bss_request4quote/request4quote_email_config/quote_extension_complete';
    const PATH_REQUEST4QUOTE_CANCELLED = 'bss_request4quote/request4quote_email_config/quote_extension_cancelled';
    const PATH_REQUEST4QUOTE_QUOTE_REJECTED = 'bss_request4quote/request4quote_email_config/quote_extension_rejected';
    const PATH_REQUEST4QUOTE_QUOTE_EXPIRED = 'bss_request4quote/request4quote_email_config/quote_extension_expired';
    const PATH_REQUEST4QUOTE_QUOTE_ORDERED = 'bss_request4quote/request4quote_email_config/quote_extension_ordered';
    const PATH_REQUEST4QUOTE_QUOTE_RESUBMIT = 'bss_request4quote/request4quote_email_config/quote_extension_resubmit';
    const PATH_REQUEST4QUOTE_QUOTE_REMINDER = 'bss_request4quote/request4quote_email_config/quote_extension_reminder_expired';

    /**
     * @var QuoteExtensionCollection
     */
    protected $quoteExtensionCollection;

    /**
     * @var array
     */
    protected $parentProductTypeList = ['configurable', 'grouped'];

    /**
     * @var StoreManagerInterface $storeManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * @var FormatDate
     */
    protected $emailData;

    /**
     * @var HidePriceEmail
     */
    protected $hidePriceEmail;

    /**
     * Mail constructor.
     * @param QuoteExtensionCollection $quoteExtensionCollection
     * @param Context $context
     * @param StoreManagerInterface $storeManagerInterface
     * @param Data $helper
     * @param LayoutInterface $layout
     * @param StateInterface $inlineTranslation
     * @param ManagerInterface $messageManager
     * @param TransportBuilder $transportBuilder
     * @param SenderResolverInterface $senderResolver
     * @param FormatDate $emailData
     * @param HidePriceEmail $hidePriceEmail
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
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
        HidePriceEmail $hidePriceEmail
    ) {
        $this->quoteExtensionCollection = $quoteExtensionCollection;
        parent::__construct($context);
        $this->storeManager = $storeManagerInterface;
        $this->helper = $helper;
        $this->layout = $layout;
        $this->inlineTranslation = $inlineTranslation;
        $this->messageManager = $messageManager;
        $this->transportBuilder = $transportBuilder;
        $this->senderResolver = $senderResolver;
        $this->emailData = $emailData;
        $this->hidePriceEmail = $hidePriceEmail;
    }

    /**
     * Get Sender Email
     *
     * @return mixed
     * @throws MailException
     */
    public function getEmailSender()
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_EMAIL_IDENTITY,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->senderResolver->resolve($from);
        return $result['email'];
    }

    /**
     * Get Sender Name
     *
     * @return mixed
     * @throws MailException
     */
    public function getEmailSenderName()
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_EMAIL_IDENTITY,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->senderResolver->resolve($from);
        return $result['name'];
    }

    /**
     * Get Email copy to
     *
     * @return array
     */
    public function getEmailCoppy()
    {
        $sendEmailCoppys = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_EMAIL_COPY,
            ScopeInterface::SCOPE_STORE
        );
        if ($sendEmailCoppys != '') {
            return $this->helper->toArray($sendEmailCoppys);
        }
        return [];
    }

    /**
     * Get email for new quote config
     *
     * @return mixed
     */
    public function getEmailNewQuote()
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_NEW_QUOTE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get email for new quote config
     *
     * @return mixed
     */
    public function getEmailNewQuoteForCustomer()
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_NEW_QUOTE_CUSTOMER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get email for receive quote config
     *
     * @return mixed
     * @throws MailException
     */
    public function getEmailReceiveEmail()
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_RECEIVE_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->senderResolver->resolve($from);
        return $result['email'];
    }

    /**
     * Get email for receive quote config
     *
     * @return mixed
     * @throws MailException
     */
    public function getEmailReceiveEmailName()
    {
        $from = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_RECEIVE_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->senderResolver->resolve($from);
        return $result['name'];
    }

    /**
     * Get email for cancel quote config
     *
     * @return mixed
     */
    public function getEmailCancelledQuote()
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_CANCELLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get email for reject quote config
     *
     * @return mixed
     */
    public function getEmailRejectedQuote()
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_REJECTED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get email for resubmit quote config
     *
     * @return mixed
     */
    public function getEmailResubmitQuote()
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_RESUBMIT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get email for accept quote config
     *
     * @return mixed
     */
    public function getEmailAcceptQuote()
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_ACCEPT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get email for complete quote config
     *
     * @return mixed
     */
    public function getEmailCompleteQuote()
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_COMPLETE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get email for expired quote config
     *
     * @return mixed
     */
    public function getEmailQuoteExpried()
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_EXPIRED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get email for ordered quote config
     *
     * @return mixed
     */
    public function getEmailQuoteOrdered()
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_ORDERED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get email for ordered quote config
     *
     * @return mixed
     */
    public function getEmailReminder()
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTE_REMINDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Send new quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationNewQuoteEmail($quote, $requestQuote)
    {
        $templateName = $this->getEmailNewQuote();
        $senderEmail = $this->getEmailSender();
        if ($senderEmail) {
            $quote->setIsAdminNotification(true);
            $senderName = is_string(__('Customer ')) ? __('Customer ') : __('Customer ')->getText();
            $recipientEmail = $this->getEmailReceiveEmail();
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'created_at' => $this->emailData->getCreatedAtFormatted(
                    $quote->getCreatedAt(),
                    $quote->getstore(),
                    IntlDateFormatter::MEDIUM
                ),
                'quote' => $quote
            ];
            $storeId = $this->storeManager->getStore()->getId();
            $recipientEmail = $this->getRecipientsEmail($recipientEmail);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send new quote email for customer
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationNewQuoteEmailForCustomer($quote, $requestQuote)
    {
        $templateName = $this->getEmailNewQuoteForCustomer();
        $senderEmail = $this->getEmailSender();
        $quote = $this->checkHidePrice($requestQuote, $quote);
        if ($senderEmail) {
            $quote->setIsAdminNotification(false);
            $senderName = $this->getEmailSenderName();
            $recipientEmail = $this->getUser($quote)["email"];
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'quote' => $quote
            ];
            $storeId = $this->storeManager->getStore()->getId();
            $recipientEmail = $this->getRecipientsEmail($recipientEmail);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send accept quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Exception
     */
    public function sendNotificationAcceptQuoteEmail($quote, $requestQuote)
    {
        $templateName = $this->getEmailAcceptQuote();
        $senderEmail = $this->getEmailSender();

        if ($senderEmail) {
            $recipientEmail = $this->getUser($quote)["email"];
            $requestQuoteUrl = $this->_getUrl(
                "quoteextension/quote/view",
                [
                    'quote_id' => $requestQuote->getId(),
                    'token' => $requestQuote->getToken()
                ]
            );
            $quote->setNeedHidePrice(false);
            $variables = $this->getVariables($requestQuote, $quote, $requestQuoteUrl);
            $storeId = $this->storeManager->getStore()->getId();
            $senderName = $this->getEmailSenderName();
            $recipientEmail = $this->getRecipientsEmail($recipientEmail);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send complete quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Exception
     */
    public function sendNotificationCompleteQuoteEmail($quote, $requestQuote)
    {
        $templateName = $this->getEmailCompleteQuote();
        $senderEmail = $this->getEmailSender();

        if ($senderEmail) {
            $recipientEmail = $this->getUser($quote)["email"];
            $requestQuoteUrl = $this->_getUrl(
                "quoteextension/quote/view",
                [
                    'quote_id' => $requestQuote->getId(),
                ]
            );
            $quote->setNeedHidePrice(false);
            $variables = $this->getVariables($requestQuote, $quote, $requestQuoteUrl);
            $storeId = $this->storeManager->getStore()->getId();
            $senderName = $this->getEmailSenderName();
            $recipientEmail = $this->getRecipientsEmail($recipientEmail);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send cancel quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationQuoteCancelledEmail($quote, $requestQuote)
    {
        $currentDate = $this->emailData->getCurrentDate();
        $cancelDate = $this->emailData->getCreatedAtFormatted(
            $currentDate,
            $quote->getstore(),
            IntlDateFormatter::MEDIUM
        );
        $templateName = $this->getEmailCancelledQuote();
        $senderEmail = $this->getEmailSender();

        if ($senderEmail) {
            $senderName = $this->getEmailSenderName();
            $getUser = $this->getUser($quote);
            $recipientEmail = $getUser["email"];
            $recipientName = $getUser["name"];
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'cancelled_date' => $cancelDate,
                'customer_name' => $recipientName,
                'quote' => $quote
            ];
            $storeId = $this->storeManager->getStore()->getId();
            $recipientEmail = $this->getRecipientsEmail($recipientEmail);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send ordered quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationQuoteOrderedEmail($quote, $requestQuote)
    {
        $templateName = $this->getEmailQuoteOrdered();
        $senderEmail = $this->getEmailSender();

        if ($senderEmail) {
            $senderName = $this->getEmailSenderName();
            $recipientEmail = $this->getEmailReceiveEmail();
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'quote' => $quote
            ];
            $storeId = $this->storeManager->getStore()->getId();
            $recipientEmail = $this->getRecipientsEmail($recipientEmail);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send reject quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationQuoteRejectedEmail($quote, $requestQuote)
    {
        $currentDate = $this->emailData->getCurrentDate();
        $cancelDate = $this->emailData->getCreatedAtFormatted(
            $currentDate,
            $quote->getstore(),
            IntlDateFormatter::MEDIUM
        );
        $templateName = $this->getEmailRejectedQuote();
        $senderEmail = $this->getEmailSender();

        if ($requestQuote->getStatus() === Status::STATE_PENDING
            || $requestQuote->getStatus() === Status::STATE_CANCELED
            || $requestQuote->getStatus() === Status::STATE_REJECTED
        ) {
            foreach ($quote->getAllVisibleItems() as $item) {
                /* @var $item Item */
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

        $requestQuoteUrl = $this->_getUrl(
            "quoteextension/quote/view",
            [
                'quote_id' => $requestQuote->getId(),
                'token' => $requestQuote->getToken()
            ]
        );

        if ($senderEmail) {
            $senderName = is_string(__('Admin ')) ? __('Admin ') : __('Admin ')->getText();
            $getUser = $this->getUser($quote);
            $recipientEmail = $getUser["email"];
            $recipientName = $getUser["name"];
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'cancelled_date' => $cancelDate,
                'customer_name' => $recipientName,
                'quote' => $quote,
                'request_quote' => $requestQuote,
                'request_url' => $requestQuoteUrl
            ];
            $storeId = $this->storeManager->getStore()->getId();
            $recipientEmail = $this->getRecipientsEmail($recipientEmail);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send resubmit quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationQuoteReSubmitEmail($quote, $requestQuote)
    {
        $templateName = $this->getEmailResubmitQuote();
        $senderEmail = $this->getEmailSender();
        $updateAt = $requestQuote->getUpdatedAt();
        if ($senderEmail) {
            $quote->setIsAdminNotification(true);
            $senderName = $this->getEmailSenderName();
            $recipientEmail = $this->getEmailReceiveEmail();
            $recipientName  = $this->helper->getCustomerName($requestQuote->getCustomerId());
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'update_date' => $updateAt,
                'customer_name' => $recipientName,
                'quote' => $quote
            ];
            $storeId = $this->storeManager->getStore()->getId();
            $recipientEmail = $this->getRecipientsEmail($recipientEmail);
            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send reminder quote email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendNotificationQuoteReminderEmail($quote, $requestQuote)
    {
        $templateName = $this->getEmailReminder();
        $senderEmail = $this->getEmailSender();
        $expiryDay = $requestQuote->getExpiry();
        if ($senderEmail) {
            $senderName = $this->getEmailSenderName();
            $recipientEmail = $quote->getCustomerEmail();
            $recipientName  = $this->helper->getCustomerName($requestQuote->getCustomerId());
            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'expired_day' => $expiryDay,
                'customer_name' => $recipientName,
                'quote' => $quote
            ];
            $storeId = $this->storeManager->getStore()->getId();
            $recipientEmail = $this->getRecipientsEmail($recipientEmail);

            /* Send additional Email Reminder To Admin */
            if (is_array($recipientEmail)) {
                $recipientEmail[] = $this->getEmailReceiveEmail();
            } else {
                $recipientEmail = [$recipientEmail, $this->getEmailReceiveEmail()];
            }

            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Send Expired Email
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @throws MailException
     * @throws NoSuchEntityException|LocalizedException
     */
    public function sendNotificationExpiredEmail($quote, $requestQuote)
    {
        $templateName = $this->getEmailQuoteExpried();
        $senderEmail = $this->getEmailSender();

        if ($senderEmail) {
            $recipientEmail = $this->getUser($quote)["email"];
            $url = $this->storeManager->getStore()->getUrl();

            $quote = $this->checkHidePrice($requestQuote, $quote);

            $variables = [
                'increment_id' => $requestQuote->getIncrementId(),
                'created_at' => $this->emailData->getCreatedAtFormatted(
                    $quote->getCreatedAt(),
                    $quote->getstore(),
                    IntlDateFormatter::MEDIUM
                ),
                'quote' => $quote,
                'purchase_link' => $url,
                'expired_at' => $this->emailData->formatDate($requestQuote->getExpiry(), IntlDateFormatter::SHORT)
            ];

            $storeId = $this->storeManager->getStore()->getId();
            $senderName = $this->getEmailSenderName();
            $recipientEmail = $this->getRecipientsEmail($recipientEmail);

            /* Send additional Email Expired To Admin */
            if (is_array($recipientEmail)) {
                $recipientEmail[] = $this->getEmailReceiveEmail();
            } else {
                $recipientEmail = [$recipientEmail, $this->getEmailReceiveEmail()];
            }

            $this->send(
                $templateName,
                $senderName,
                $senderEmail,
                $recipientEmail,
                $variables,
                $storeId
            );
        }
    }

    /**
     * Get other email to sender
     *
     * @param string|array $recipientEmail
     * @return array
     */
    protected function getRecipientsEmail($recipientEmail)
    {
        $emailCoppys = $this->getEmailCoppy();
        if (!empty($emailCoppys)) {
            $emailCoppys[] = $recipientEmail;
            $receivers = $emailCoppys;
            return $receivers;
        }

        return $recipientEmail;
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
     * @return bool
     */
    protected function send(
        $templateName,
        $senderName,
        $senderEmail,
        $recipientEmail,
        $variables,
        $storeId
    ) {
        $this->inlineTranslation->suspend();
        try {
            if (is_array($recipientEmail)) {
                foreach ($recipientEmail as $recipient) {
                    $this->_send(
                        $templateName,
                        $senderName,
                        $senderEmail,
                        $recipient,
                        $variables,
                        $storeId
                    );
                }
            } else {
                $this->_send(
                    $templateName,
                    $senderName,
                    $senderEmail,
                    $recipientEmail,
                    $variables,
                    $storeId
                );
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
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
     * @return void
     * @throws LocalizedException
     * @throws MailException
     */
    protected function _send(
        $templateName,
        $senderName,
        $senderEmail,
        $recipientEmail,
        $variables,
        $storeId
    ) {
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateName)
            ->setTemplateOptions([
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars($variables)
            ->setFrom([
                'name' => $senderName,
                'email' => $senderEmail
            ])
            ->addTo($recipientEmail)
            ->setReplyTo($senderEmail)
            ->getTransport();

        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * Get sub user or owner user email
     *
     * @param CartInterface $quote
     * @return array
     * @throws LocalizedException
     */
    public function getUser($quote)
    {
        $quoteId = $quote->getId();
        $customerEmail = $quote->getCustomerEmail();
        if ($this->helper->isEnableCompanyAccount()) {
            $quoteExtension = $this->quoteExtensionCollection->create();
            if ($quote->getIsAdminSubmitted()) {
                $quoteExtension = $quoteExtension->addFieldToFilter("main_table.backend_quote_id", $quoteId)->getLastItem();
            } else {
                $quoteExtension = $quoteExtension->addFieldToFilter("main_table.quote_id", $quoteId)->getLastItem();
            }
            $subUserEmail = $quoteExtension->getSubEmail();
            if ($subUserEmail) {
                return [
                    "name" => $quoteExtension->getSubName(),
                    "email" => $subUserEmail
                ];
            }
        }
        return [
            "name" => $this->helper->getCustomerName($quote->getCustomerId()),
            "email" => $customerEmail
        ];
    }

    /**
     * Get Variables
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @param string $requestQuoteUrl
     * @return array
     * @throws Exception
     */
    public function getVariables($requestQuote, $quote, $requestQuoteUrl)
    {
        return [
            'increment_id' => $requestQuote->getIncrementId(),
            'created_at' => $this->emailData->getCreatedAtFormatted(
                $quote->getCreatedAt(),
                $quote->getstore(),
                IntlDateFormatter::MEDIUM
            ),
            'request_url' => $requestQuoteUrl,
            'requestQuote' => $requestQuote,
            'quote' => $quote
        ];
    }

    /**
     * Get Quote id
     *
     * @param \Bss\QuoteExtension\Model\ManageQuote $requestQuote
     * @return mixed
     */
    public function getQuoteId($requestQuote)
    {
        return $this->helper->getQuoteId($requestQuote);
    }

    /**
     * Check hide price when cron run
     *
     * @param CartInterface $quote
     * @param ManageQuote $requestQuote
     * @return mixed
     */
    public function checkHidePrice($requestQuote, $quote)
    {
        if ($requestQuote->getStatus() === Status::STATE_PENDING
            || $requestQuote->getStatus() === Status::STATE_CANCELED
            || $requestQuote->getStatus() === Status::STATE_REJECTED
        ) {
            foreach ($quote->getAllVisibleItems() as $item) {
                /* @var $item Item */
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
        } else {
            $quote->setNeedHidePrice(false);
        }
        return $quote;
    }
}
