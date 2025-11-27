<?php

namespace Kos\CustomBssQuote\Model\Quote\Item;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Zend\Code\Exception\InvalidArgumentException;


class Updater extends \Magento\Quote\Model\Quote\Item\Updater
{
	protected $helper;
	 
	public function __construct(
        ProductFactory $productFactory,
        FormatInterface $localeFormat,
        ObjectFactory $objectFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        \Kos\CustomBssQuote\Helper\Data $helper
    ) {
    	$this->_helper = $helper;
        parent::__construct($productFactory,$localeFormat,$objectFactory,$serializer);
    }

	public function update(Item $item, array $info)
    {
        if (!isset($info['qty'])) {
            throw new InvalidArgumentException(__('The qty value is required to update quote item.'));
        }
        $itemQty = $info['qty'];
        if ($item->getProduct()->getStockItem()) {
            if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                $itemQty = (int)$info['qty'];
            } else {
                $item->setIsQtyDecimal(1);
            }
        }
        $itemQty = $itemQty > 0 ? $itemQty : 1;
        if (isset($info['custom_price'])) {
            $this->setCustomPrice($info, $item);
        } elseif ($item->hasData('custom_price')) {
            $this->unsetCustomPrice($item);
        }

        if (isset($info['change_day_to_ship'])) {
            $item->setChangeDayToShip($info['change_day_to_ship']);
            $item->setDayToShip($info['day_to_ship']);
        }else {
            $item->setChangeDayToShip(null);
            $item->setDayToShip($this->_helper->getDayToShip($itemQty,$item));
        }

        if (empty($info['action']) || !empty($info['configured'])) {
            $noDiscount = !isset($info['use_discount']);
            $item->setQty($itemQty);
            $item->setNoDiscount($noDiscount);
            $item->getProduct()->setIsSuperMode(true);
            $item->getProduct()->unsSkipCheckRequiredOption();
            $item->checkData();
        }

        return $this;
    }
}