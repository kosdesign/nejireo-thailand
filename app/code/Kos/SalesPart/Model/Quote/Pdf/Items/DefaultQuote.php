<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kos\SalesPart\Model\Quote\Pdf\Items;

class DefaultQuote extends \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
{
    /**
     * Quote model
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * @var int
     */
    protected $countItem = 0;

    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Kos\SalesPart\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * DefaultQuote constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Kos\SalesPart\Helper\Data $helperData
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Kos\SalesPart\Helper\Data $helperData,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->string = $string;
        $this->currencyFactory = $currencyFactory;
        $this->helperData = $helperData;
        $this->productRepository = $productRepository;
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    /**
     * @param $count
     * @return $this
     */
    public function setItemCount($count)
    {
        $this->countItem = $count;
        return $this;
    }

    /**
     * Retrieve quote object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (null === $this->_quote) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The quote object is not specified.'));
        }
        return $this->_quote;
    }

    /**
     * Draw item line
     *
     * @return void
     */
    public function draw()
    {
        $quote = $this->getQuote();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];
        $fontSize = 8;

        $product = $item->getProduct();
        if($product->getTypeId() == 'configurable') {
            try {
                $product = $this->productRepository->get($item->getSku());
            } catch (\Exception $e) {}
        }

        $productData = $this->helperData->getProductData($product);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        if ($productData['part_number']) {
            $skuHeight = $this->helperData->calcValueHeight($productData['part_number'], 9);
        } else {
            $skuHeight = 0;
        }

        if ($productData['plating']) {
            $platingHeight = $this->helperData->calcValueHeight($productData['plating'], 10);
        } else {
            $platingHeight = 0;
        }

        if ($productData['material']) {
            $materialHeight = $this->helperData->calcValueHeight($productData['material'], 8);
        } else {
            $materialHeight = 0;
        }

        $nameHeight = $this->helperData->calcValueHeight($product->getName(), 32);

        $regHeight = max($skuHeight, $nameHeight, $platingHeight, $materialHeight);
        $padding = 3;
        
        if ($this->countItem == 1) {
            $topY = $pdf->y - 1;
        } else {
            $topY = $pdf->y + 5;
        }

        $bottomY = $pdf->y - $padding - $regHeight;

        $page->drawRectangle(25, $topY, 570, $bottomY);
        $page->drawLine(87, $topY, 87, $bottomY);
        $page->drawLine(237, $topY, 237, $bottomY);
        $page->drawLine(282, $topY, 282, $bottomY);
        $page->drawLine(337, $topY, 337, $bottomY);
        $page->drawLine(377, $topY, 377, $bottomY);
        $page->drawLine(412, $topY, 412, $bottomY);
        $page->drawLine(442, $topY, 442, $bottomY);
        $page->drawLine(486, $topY, 486, $bottomY);
        $page->drawLine(530, $topY, 530, $bottomY);

        if ($this->countItem == 1) {
            $pdf->y -= 10;
        } else {
            $pdf->y -= 8;
        }

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        // Part number
        $partNumber = $productData['part_number'];
        $lines[0] = [
            [
                'text' => $this->string->split($partNumber, 9),
                'feed' => 30,
                'font_size' => $fontSize
            ]
        ];

        // draw Product name
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($product->getName()), 32, true, true),
            'feed' => 90, 'font_size' => $fontSize
        ];

        // draw Material
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split($productData['material'], 8, true, true),
            'feed' => 240,
            'font_size' => $fontSize
        ];

        // draw Plating
        $lines[0][] = ['text' => $this->string->split($productData['plating'], 10, true, true), 'feed' => 285, 'font_size' => $fontSize];

        // draw Diameter
        $lines[0][] = ['text' => $productData['diameter'], 'feed' => 340, 'font_size' => $fontSize];

        // draw Length
        $lines[0][] = ['text' => $productData['length'], 'feed' => 380, 'font_size' => $fontSize];

        // draw Qty
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 415, 'font_size' => $fontSize];

        // draw LeadTime
        //$dayToShip = $productData['day_to_ship'];
        $dayVal = '';
        if ($item->getDayToShip()) {
            $dayVal = strtolower($item->getDayToShip());
        }

        $lines[0][] = ['text' => $dayVal, 'feed' => 565, 'align' => 'right', 'font_size' => $fontSize];

        // draw Unit Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = 446;
        $feedSubtotal = $feedPrice + 43;
         foreach ($prices as $priceData) {
             if (isset($priceData['label'])) {
                 // draw Price label
                 $lines[$i][] = [
                     'text' => $priceData['label'],
                     'feed' => $feedPrice,
                     'font_size' => $fontSize
                 ];
                 // draw Subtotal label
                 $lines[$i][] = [
                     'text' => $priceData['label'],
                     'feed' => $feedSubtotal,
                     'font_size' => $fontSize
                 ];
                 $i++;
             }
             // draw Price
             $lines[$i][] = [
                 'text' => $priceData['price'],
                 'feed' => $feedPrice,
                 'font_size' => $fontSize
             ];
             // draw Subtotal
             $lines[$i][] = [
                 'text' => $priceData['subtotal'],
                 'feed' => $feedSubtotal,
                 'font_size' => $fontSize
             ];
             $i++;
         }

         //custom options
         $options = $this->getItemOptions();
         if ($options) {
             foreach ($options as $option) {
                 // draw options label
                 $lines[][] = [
                     'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                     'font' => 'italic',
                     'feed' => 80, 'font_size' => $fontSize
                 ];

                 // Checking whether option value is not null
                 if ($option['value'] !== null) {
                     if (isset($option['print_value'])) {
                         $printValue = $option['print_value'];
                     } else {
                         $printValue = $this->filterManager->stripTags($option['value']);
                     }
                     $values = explode(', ', $printValue);
                     foreach ($values as $value) {
                         $lines[][] = ['text' => $this->string->split($value, 30, true, true), 'feed' => 40, 'font_size' => $fontSize];
                     }
                 }
             }
         }

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->setPage($page);
    }

    /**
     * Get array of arrays with item prices information for display in PDF
     *
     * Format: array(
     *  $index => array(
     *      'label'    => $label,
     *      'price'    => $price,
     *      'subtotal' => $subtotal
     *  )
     * )
     *
     * @return array
     */
    public function getItemPricesForDisplay()
    {
        $item = $this->getItem();
        if ($this->_taxData->displaySalesBothPrices()) {
            $prices = [
                [
                    'label' => __('Excl. Tax') . ':',
                    'price' => $this->helperData->formatPriceTxt($item->getPrice(), $this->getQuote()),
                    'subtotal' => $this->helperData->formatPriceTxt($item->getRowTotal(), $this->getQuote()),
                ],
                [
                    'label' => __('Incl. Tax') . ':',
                    'price' => $this->helperData->formatPriceTxt($item->getPriceInclTax(), $this->getQuote()),
                    'subtotal' => $this->helperData->formatPriceTxt($item->getRowTotalInclTax(), $this->getQuote())
                ],
            ];
        } elseif ($this->_taxData->displaySalesPriceInclTax()) {
            $prices = [
                [
                    'price' => $this->helperData->formatPriceTxt($item->getPriceInclTax(), $this->getQuote()),
                    'subtotal' => $this->helperData->formatPriceTxt($item->getRowTotalInclTax(), $this->getQuote()),
                ],
            ];
        } else {
            $prices = [
                [
                    'price' => $this->helperData->formatPriceTxt($item->getPrice(), $this->getQuote()),
                    'subtotal' => $this->helperData->formatPriceTxt($item->getRowTotal(), $this->getQuote()),
                ],
            ];
        }
        return $prices;
    }

    /**
     * Retrieve item options
     *
     * @return array
     */
    public function getItemOptions()
    {
        $result = [];
        $options = $this->getItem()->getOptionByCode('additional_options');
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        return $result;
    }
}
