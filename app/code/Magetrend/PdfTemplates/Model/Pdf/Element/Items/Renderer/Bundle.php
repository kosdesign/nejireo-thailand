<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer;

/**
 * Bundle item pdf renderer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Bundle extends \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer\DefaultRenderer
{
    /**
     * Returns formated subtotal value
     *
     * @return string
     */
    public function getFormatedSubtotal()
    {
        $priceForDisplay = $this->getItemPricesForDisplay();

        $item =  $this->getItem();
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $qty = (int)$item->getQtyOrdered();
        } else {
            $qty = (int)$item->getQty();
        }

        $rowTotal = $priceForDisplay[0]['price'] * $qty;
        return $this->getOrder()->formatPriceTxt($rowTotal);
    }

    public function getBundleItemOptions()
    {
        $bundleOptions = [];
        $item = $this->getItem();
        $order = $this->getOrder();
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }

        if ($options && isset($options['bundle_options'])) {
            foreach ($options['bundle_options'] as $option) {
                foreach ($option['value'] as $subOption) {
                    $bundleOptions[] = [
                        'label' => $subOption['title'],
                        'value' => $subOption['qty'].' x '.$order->formatPriceTxt($subOption['price'])
                    ];
                }
            }
        }

        return $bundleOptions;
    }

    public function getFormatedItemOptions()
    {
        $optionsString = parent::getFormatedItemOptions();
        if (!empty($optionsString)) {
            $optionsString = '{br}';
        }
        $options = $this->getBundleItemOptions();
        $counter = count($options);
        foreach ($options as $key => $option) {
            $value = $this->decorator->addDecorator(
                $option['value'],
                \Magetrend\PdfTemplates\Model\Pdf\Decorator::TYPE_COLOR,
                'table_row_product_line_2_value_color'
            );

            $optionsString.= $option['label'].': ' .$value;
            if ($counter - 1 != $key) {
                $optionsString.= '{br}';
            }
        }

        return $optionsString;
    }
}
