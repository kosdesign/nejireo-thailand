<?php
namespace Kos\SalesPart\Model\Quote;

use Bss\QuoteExtension\Model\Pdf\PrintPdf;
use Magento\MediaStorage\Helper\File\Storage\Database;

/**
 * Class Pdf
 * @package Kos\SalesPart\Model\Quote
 */
class Pdf extends PrintPdf
{
    /**
     * @var mixed
     */
    private $fileStorageDatabase;

    /**
     * @var \Kos\SalesPart\Helper\Data
     */
    protected $helperData;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Bss\QuoteExtension\Model\Quote\Address\Renderer $addressRenderer,
        \Bss\QuoteExtension\Model\Pdf\Items\QuoteItem $renderer,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        \Bss\QuoteExtension\Helper\CartHidePrice $cartHidePrice,
        \Kos\SalesPart\Helper\Data $helperData,
        array $data = [],
        Database $fileStorageDatabase = null
    ) {
        $this->helperData = $helperData;
        $this->fileStorageDatabase = $fileStorageDatabase ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(Database::class);
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $renderer,
            $manageQuote,
            $cartHidePrice,
            $data
        );
    }

    /**
     * Return PDF document
     *
     * @param  array $quotes
     *
     * @return \Zend_Pdf
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Pdf_Exception
     */
    public function getPdf($quotes = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('kos_quote_extend');

        $quotesArr = $quotes;
        if (isset($quotes['data'])) {
            $quotesArr = $quotes['quote'];
        }

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($quotesArr as $order) {
            $page = $this->newPage();
            $customer = $this->helperData->getCustomer($order->getCustomerId());
            $customerGroup = $this->helperData->getCustomerGroupNameById($customer->getGroupId());
            $quoteRequest = [
                'incrementId' => $order->getId(),
                'from' => 'other'
            ];

            if (isset($quotes['data'])) {
                $quoteRequest = $quotes['data'];
            }

            $this->drawTopHeader($page);
            /* add Quote number and date to header */
            $this->insertQuoteNumber($page, $order, $quoteRequest);
            /* Add Person in chair */
            $this->insertPerSonInChair($page, null, $customerGroup);

            /* Add image */
            $this->insertLogo($page, $order->getStore());
            /* add customer infor block to header */
            $this->insertCustomerQuote($page, $order, null, $customerGroup);
            /* Add address */
            $this->insertAddress($page, $order->getStore());
            $this->_drawHeader($page);

            /* Add body */
            $i = 0;
            foreach ($order->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                /* Draw item */
                $i++;
                $this->_drawQuoteItem($item, $page, $order, $i);
                $page = end($pdf->pages);
            }

            /* Add totals */
            $page = $this->insertTotals($page, $order);
            $this->insertSignature($page);

            $this->insertTermAndCondition($page, null, $customerGroup);
            $this->insertBankInfor($page, null, $customerGroup);
        }
        
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Draw header for item table
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $page->drawLine(87, $this->y, 87, $this->y - 15);
        $page->drawLine(237, $this->y, 237, $this->y - 15);
        $page->drawLine(282, $this->y, 282, $this->y - 15);
        $page->drawLine(337, $this->y, 337, $this->y - 15);
        $page->drawLine(377, $this->y, 377, $this->y - 15);
        $page->drawLine(412, $this->y, 412, $this->y - 15);
        $page->drawLine(442, $this->y, 442, $this->y - 15);
        $page->drawLine(486, $this->y, 486, $this->y - 15);
        $page->drawLine(530, $this->y, 530, $this->y - 15);

        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $fontSize = 8;
        $lines[0][] = ['text' => __('Part Number'), 'align' => 'center', 'feed' => 30, 'font_size' => $fontSize];
        $lines[0][] = ['text' => __('Name'), 'feed' => 90, 'align' => 'center', 'font_size' => $fontSize];
        $lines[0][] = ['text' => __('Material'), 'feed' => 240, 'align' => 'center', 'font_size' => $fontSize];
        $lines[0][] = ['text' => __('Plating'), 'feed' => 285, 'align' => 'center', 'font_size' => $fontSize];
        $lines[0][] = ['text' => __('Diameter'), 'feed' => 340, 'align' => 'center', 'font_size' => $fontSize];
        $lines[0][] = ['text' => __('Length'), 'feed' => 380, 'align' => 'center', 'font_size' => $fontSize];
        $lines[0][] = ['text' => __('Qty'), 'feed' => 415, 'align' => 'center', 'font_size' => $fontSize];
        $lines[0][] = ['text' => __('Unit Price'), 'feed' => 445, 'align' => 'center', 'font_size' => $fontSize];
        $lines[0][] = ['text' => __('Amount'), 'feed' => 515, 'align' => 'right', 'font_size' => $fontSize];
        $lines[0][] = ['text' => __('Leadtime'), 'feed' => 565, 'align' => 'right', 'font_size' => $fontSize];
        $lineBlock = ['lines' => $lines, 'height' => 6];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= -2;
    }

    /**
     * Draw Item process
     *
     * @param  \Magento\Framework\DataObject $item
     * @param  \Zend_Pdf_Page $page
     * @param  \Magento\Quote\Model\Quote $quote
     * @return \Zend_Pdf_Page
     */
    protected function _drawQuoteItem(
        \Magento\Framework\DataObject $item,
        \Zend_Pdf_Page $page,
        \Magento\Quote\Model\Quote $quote,
        $i
    ) {

        $type = $item->getProductType();
        $renderer = $this->_getRenderer($type);
        $renderer->setQuote($quote);
        $renderer->setItem($item);
        $renderer->setPdf($this);
        $renderer->setPage($page);
        $renderer->setItemCount($i);
        $renderer->setRenderedModel($this);

        $renderer->draw();

        return $renderer->getPage();
    }

    /**
     * Insert address to pdf page
     *
     * @param \Zend_Pdf_Page $page
     * @param string|null $store
     * @return void
     */
    protected function insertAddress(&$page, $store = null)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($page, 9);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = 765;
        $values = explode(
            "\n",
            $this->_scopeConfig->getValue(
                'sales/identity/address',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
        );
        foreach ($values as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(
                        trim(strip_tags($_value)),
                        $this->getAlignRight($_value, 380, 190, $font, 9),
                        $top,
                        'UTF-8'
                    );
                    $top -= 14;
                }
            }
        }
        $this->_setFontRegular($page, 8);
        $this->y = $this->y > $top ? $top : $this->y;
    }

    /**
     * @param $page
     * @param null $store
     * @param $customerGroup
     */
    protected function insertPerSonInChair(&$page, $store = null, $customerGroup = 'B2B')
    {
        $txtLine10 = __("Person in charge");
        $top = 640;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        $y0 = $top - 10;
        $y1 = $y0 - 10;
        $y2 = $y1 - 10;

        $perSonName = $this->helperData->getPerSonName($store, $customerGroup);
        $perSonPhone = $this->helperData->getPerSonPhone($store, $customerGroup);
        $page->drawRectangle(450, $top + 8, 560, $top - 40);
        $page->drawLine(450, $y0, 560, $y0);

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        $font = $this->_setFontRegular($page, 9);
        $page->drawText(
            trim(strip_tags($txtLine10)),
            $this->getAlignCenter($txtLine10, 450, 120, $font, 10),
            $top-2,
            'UTF-8'
        );
        $page->drawText(
            trim(strip_tags($perSonName)),
            $this->getAlignCenter($perSonName, 450, 120, $font, 10),
            $y1,
            'UTF-8'
        );
        $page->drawText(
            trim(strip_tags($perSonPhone)),
            $this->getAlignCenter($perSonPhone, 450, 120, $font, 10),
            $y2,
            'UTF-8'
        );
    }

    /**
     * @param $page
     * @param $quote
     * @param null $store
     * @param string $storeName
     */
    protected function insertCustomerQuote(&$page, $quote, $store = null, $storeName = 'B2B')
    {
        $paymentCondition = $this->helperData->getPaymentCondition($store, $storeName);
        $validity = $this->helperData->getValidity($store, $storeName);
        $this->y = $this->y ? $this->y : 815;
        $this->_setFontRegular($page, 8);
        $y1 = $this->y - 20;
        $y2 = $y1 - 21;
        $y3 = $y2 - 18;
        $y4 = $y3 - 22;
        $y5 = $y4 - 20;
        $y6 = $y5 - 20;
        $y7 = $y6 - 33;
        $y8 = $y7 - 4;
        $y9 = $y8 - 18;
        $y10 = $y9 - 4;
        $page->setLineWidth(0.5);
        $page->drawText(__('To'), 25, $y1, 'UTF-8');

        $page->drawText($this->helperData->getCustomerName($quote->getCustomerId()), 25, $y2+3, 'UTF-8');
        $page->drawLine(25, $y2, 230, $y2);

        $page->drawText($this->helperData->getCustomerAddress($quote->getCustomerId()), 25, $y3+3, 'UTF-8');
        $page->drawLine(25, $y3, 230, $y3);

        $page->drawText(__('Company: '), 25, $y4, 'UTF-8');
        $page->drawText($this->helperData->getCustomerCompany($quote->getCustomerId()), 60, $y4, 'UTF-8');
        $page->drawText(__('TEL: +'), 25, $y5, 'UTF-8');
        $page->drawText($this->helperData->getCustomerPhone($quote->getCustomerId()), 50, $y5, 'UTF-8');
        $page->drawText(__('Tax ID:'), 25, $y6, 'UTF-8');
        $page->drawText($this->helperData->getCustomerVAT($quote->getCustomerId()), 55, $y6, 'UTF-8');

        $page->drawText(__('Payment Condition: '), 25, $y7, 'UTF-8');
        $page->drawText($paymentCondition, 100, $y7, 'UTF-8');
        $page->drawLine(25, $y8, 230, $y8);

        $page->drawText(__('Validity: '), 25, $y9, 'UTF-8');
        $page->drawText($validity, 70, $y9, 'UTF-8');
        $page->drawLine(25, $y10, 230, $y10);

        $this->y = $y10 - 30;
    }

    /**
     * @param $page
     */
    protected function drawTopHeader(&$page)
    {
        $this->y = $this->y ? $this->y : 815;
        $top = 815;
        $txtHeader = __("Quotation");
        $font = $this->_setFontRegular($page, 20);
        $page->drawText(
            trim(strip_tags($txtHeader)),
            $this->getAlignCenter($txtHeader, 100, 440, $font, 35),
            $top,
            'UTF-8'
        );
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawLine(250, $this->y+12.5, 335, $this->y+12.5);
                    // start, top-start, stop, top-end
        $page->setLineWidth(1);
        $page->drawLine(250, $this->y+11, 335, $this->y+11);
                     // start, top-start, stop, top-end
    }

    /**
     * @param $page
     * @param null $quote
     * @param null $quoteRequest
     */
    protected function insertQuoteNumber(&$page, $quote = null, $quoteRequest = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $top = 815;

        if ($quoteRequest && isset($quoteRequest['from']) && $quoteRequest['from'] == 'other') {
            $requestQuote = $this->manageQuote->load($quote->getId(), 'quote_id');
            if (!$requestQuote->getId()) {
                $requestQuote = $this->manageQuote->load($quote->getId(), 'backend_quote_id');
            }
            $quoteNo = $requestQuote->getIncrementId();
        } else {
            $requestQuote = null;
            $quoteNo = $quoteRequest['incrementId'];
        }

        $quoteNo = __("QTNO: #") . $quoteNo;
        $quoteDate = $this->_localeDate->formatDate(
            $this->_localeDate->scopeDate(
                $quote->getStore(),
                ($requestQuote && $requestQuote->getCreatedAt())? $requestQuote->getCreatedAt(): $quote->getCreatedAt(),
                true
            ),
            \IntlDateFormatter::MEDIUM,
            false
        );
        $font = $this->_setFontRegular($page, 9);
        $page->drawText(
            trim(strip_tags($quoteNo)),
            $this->getAlignRight($quoteNo, 400, 170, $font, 9),
            $top,
            'UTF-8'
        );
        $y2 = $top - 13;
        $page->drawText(
            trim(strip_tags($quoteDate)),
            $this->getAlignRight($quoteDate, 400, 170, $font, 9),
            $y2,
            'UTF-8'
        );
    }

    /**
     * Insert logo to pdf page
     *
     * @param \Zend_Pdf_Page $page
     * @param string|null $store
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Zend_Pdf_Exception
     */
    protected function insertLogo(&$page, $store = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $image = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($image) {
            $imagePath = '/sales/store/logo/' . $image;
            if ($this->fileStorageDatabase->checkDbUsage() &&
                !$this->_mediaDirectory->isFile($imagePath)
            ) {
                $this->fileStorageDatabase->saveFileToFilesystem($imagePath);
            }
            if ($this->_mediaDirectory->isFile($imagePath)) {
                $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                $top = 830;
                //top border of the page
                $widthLimit = 100;
                //half of the page width
                $heightLimit = 120;
                //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth();
                $height = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $top - ($height + 50);
                $y2 = $top - 50;
                $x1 = 300;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);
            }
        }
    }

    /**
     * @param $page
     * @param null $store
     */
    protected function insertSignature(&$page, $store = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $txtSignature = __("Customer authorized signature");
        //$txtStoreName = __("HANSHIN NEJI ( THAILAND ) CO.,LTD.");
        $txtStoreName = __("HANSHIN NEJI (THAILAND) LTD.");
	$font = $this->_setFontRegular($page, 8);

        $topSig = $this->y + 16;
        $page->drawText(
            trim(strip_tags($txtSignature)),
            $this->getAlignCenter($txtSignature, 35, 155, $font, 8),
            $topSig,
            'UTF-8'
        );
        $page->drawText(
            trim(strip_tags($txtStoreName)),
            $this->getAlignCenter($txtStoreName, 135, 300, $font, 8),
            $topSig,
            'UTF-8'
        );

        $topLineBottom = $topSig + 9;
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawLine(35, $topLineBottom, 180, $topLineBottom);
        $page->drawLine(200, $topLineBottom, 365, $topLineBottom);

        $this->y = $topLineBottom - 10;

        $image = $this->helperData->getSignature($store);
        if ($image) {
            $imagePath = '/sales/quote/logo/' . $image;
            if ($this->fileStorageDatabase->checkDbUsage() &&
                !$this->_mediaDirectory->isFile($imagePath)
            ) {
                $this->fileStorageDatabase->saveFileToFilesystem($imagePath);
            }
            if ($this->_mediaDirectory->isFile($imagePath)) {
                $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                //top border of the page
                $widthLimit = 100;
                //half of the page width
                $heightLimit = 50;
                //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth();
                $height = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $topSignature = $this->y + 70;

                $y1 = $topSignature - ($height + 25);
                $y2 = $topSignature - 25;
                $x1 = 235;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $topSignature - 85;
            }
        }
    }

    /**
     * @param $page
     * @param null $store
     * @param string $storeName
     */
    protected function insertTermAndCondition(&$page, $store = null, $storeName = "B2B")
    {
        $this->y = $this->y ? $this->y - 5 : 815;
        $top = 815;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        $termAndCondition = $this->helperData->getTermAndCondition($store, $storeName);
        $termAndConditionHeight = $this->helperData->calcValueHeight($termAndCondition, 170);
        $page->drawRectangle(25, $this->y + 10, 570, $this->y - 10 - $termAndConditionHeight);

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $values = explode("\n", $termAndCondition);
        $page->drawText(
            __("Terms & Condition"),
            30,
            $this->y,
            'UTF-8'
        );
        $this->y -= 15;
        foreach ($values as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 170, true, true) as $_value) {
                    $page->drawText(
                        trim(strip_tags($_value)),
                        30,
                        $this->y,
                        'UTF-8'
                    );
                    $this->y -= 12;
                }
            }
        }
        $this->y = $this->y > $top ? $top : $this->y;
    }

    /**
     * @param $page
     * @param null $store
     */
    protected function insertBankInfor(&$page, $store = null, $storeName = 'B2B')
    {
        $this->y = $this->y ? $this->y - 10 : 815;
        $top = 815;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        $bankInfo = $this->helperData->getBankInfor($store, $storeName);
        if ($bankInfo) {
            $bankInfoHeight = $this->helperData->calcValueHeight($bankInfo, 170) + 5;
            $page->drawRectangle(25, $this->y + 10, 300, $this->y - $bankInfoHeight);

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
            $values = explode("\n", $bankInfo);
            $page->drawText(
                __("Bank information"),
                30,
                $this->y,
                'UTF-8'
            );
            $this->y -= 15;
            foreach ($values as $value) {
                if ($value !== '') {
                    $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                    foreach ($this->string->split($value, 170, true, true) as $_value) {
                        $page->drawText(
                            trim(strip_tags($_value)),
                            30,
                            $this->y,
                            'UTF-8'
                        );
                        $this->y -= 10;
                    }
                }
            }
        }
        
        $this->y = $this->y > $top ? $top : $this->y;
    }

    /**
     * @param \Zend_Pdf_Page $page
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Zend_Pdf_Page
     */
    protected function insertTotals($page, $quote)
    {
        $totals = $this->_getTotalsList();
        $lineBlock = ['lines' => [], 'height' => 15];
        foreach ($totals as $total) {
            $total->setQuote($quote)->setSource($quote);
            if ($total->canDisplay()) {
                foreach ($total->getTotalsForDisplay() as $totalData) {
                    $lineBlock['lines'][] = [
                        [
                            'text' => $totalData['label'],
                            'feed' => 377,
                            'align' => 'center',
                            'width' => 109,
                            'height' =>20,
                            'font_size' => $totalData['font_size']
                        ],
                        [
                            'text' => $totalData['amount'],
                            'feed' => 565,
                            'align' => 'right',
                            'height' => 20,
                            'font_size' => $totalData['font_size']
                        ],
                    ];
                }
            }
        }

        $top = $this->y;
        $page->drawLine(377, $top - 21, 570, $top - 21);
        $page->drawLine(377, $top - 42, 570, $top - 42);
        $page->drawLine(377, $top - 61, 570, $top - 61);

        $this->y -= 15;
        $page = $this->drawLineBlocks($page, [$lineBlock]);
        $padding = $this->y + 10;
        $top = $top + 5;
        $page->drawLine(25, $padding, 570, $padding);
        $page->drawLine(25, $top, 25, $padding);
        $page->drawLine(377, $top, 377, $padding);
        $page->drawLine(486, $top, 486, $padding);
        $page->drawLine(570, $top, 570, $padding);
        return $page;
    }
}
