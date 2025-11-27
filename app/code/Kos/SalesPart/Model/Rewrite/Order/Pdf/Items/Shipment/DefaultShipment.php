<?php

namespace Kos\SalesPart\Model\Rewrite\Order\Pdf\Items\Shipment;

class DefaultShipment extends \Magento\Sales\Model\Order\Pdf\Items\Shipment\DefaultShipment
{

    public function draw()
    {
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Catalog\Model\Product')->loadByAttribute('sku', $this->getSku($item));

        // draw Product name
        $lines[0] = [
            [
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                'text' => $this->string->split(html_entity_decode($product->getName()), 60, true, true),
                'feed' => 100
            ]
        ];

        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 35];

        // draw SKU
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($product->getData('part_number')), 25),
            'feed' => 565,
            'align' => 'right',
        ];

        // Custom options
        $options = $this->getItemOptions();
        if ($options) {
            $text = '';$i = 0;
            foreach ($options as $option) {
                // draw options label
                $text .= $this->filterManager->stripTags($option['label']).' = ';

                // Checking whether option value is not null
                if ($option['value'] !== null) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $printValue = $this->filterManager->stripTags($option['value']);
                    }
                    $text .= $printValue;
                }
                if($i != (count($options) - 1)) :
                    $text .= ', ';
                endif;
            $i ++;}
            $lines[][] = [
                'text' => $text,
                'font' => 'italic',
                'feed' => 100,
            ];
        }

        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }
}
