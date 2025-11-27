<?php

namespace Kos\CustomBssQuote\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected $productRepository;
    protected $_customerFactory;
    protected $_addressFactory;
    protected $_groupRepository;
    protected $_customerRepository;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->productRepository = $productRepository;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_groupRepository = $groupRepository;
        $this->_customerRepository = $customerRepository;
        parent::__construct($context);
    }

    public function getCustomerCompany($customerId)
    {
        $customerHsID = 'No HS Customer ID';
        $customer = $this->_customerFactory->create()->load($customerId);
        $customerRepo = $this->_customerRepository->getById($customerId);
        if ($customerRepo->getCustomAttribute('hs_customer_id')) {
            $customerHsID = $customerRepo->getCustomAttribute('hs_customer_id')->getValue();
        }
        $billingAddressId = $customer->getDefaultBilling();
        $billingAddress = $this->_addressFactory->create()->load($billingAddressId);
        $group = $this->_groupRepository->getById($customer->getGroupId());
        $customerCompany = $billingAddress->getData('company');
        $customerGroup = $group->getCode();
        $BssQuoteCustomAttribute = ['hs_customer_id' => $customerHsID, 'company' => $customerCompany, 'customer_group' => $customerGroup];
        return $BssQuoteCustomAttribute;
    }

    public function getDayToShip($qty, $item)
    {
        $product = $item->getProduct();
        if ($product->getTypeId() == 'configurable') {
            $simpleId = $product->getIdBySku($product->getSku());
            $product = $this->productRepository->getById($simpleId);
        }
        $dayToShip = "";
        if ($product->getTierPrice()) {
            $tierPrices = $product->getTierPrice();
            $countTier = count($tierPrices);
            $qtyMin = '';
            $qtyMax = '';

            for ($i = 0; $i < $countTier; $i++) {
                if (!empty($tierPrices[$i + 1])) {
                    $qtyMin = (int)$tierPrices[$i]['price_qty'];
                    $qtyMax = (int)$tierPrices[$i + 1]['price_qty'];
                    for ($j = $qtyMin; $j < $qtyMax; $j++) {
                        if ($j == $qty) {
                            if (empty($tierPrices[$i]['day_to_ship'])) {
                                $day = $product->getDayToShip();
                            } else {
                                $day = $tierPrices[$i]['day_to_ship'];
                            }
                            $dayToShip = $dayToShip . $day;
                        }
                    }
                } else {
                    if ($qty >= $qtyMax) {
                        if (empty($tierPrices[$i]['day_to_ship'])) {
                            $day = $product->getDayToShip();
                        } else {
                            $day = $tierPrices[$i]['day_to_ship'];
                        }
                        $dayToShip = $dayToShip . $day;
                    }
                    if ($qty < $qtyMin) {
                        $day = $product->getDayToShip();
                        $dayToShip = $dayToShip . $day;
                    }
                }
            }
        } else {
            $dayToShip = $product->getDayToShip();
        }

        return $dayToShip;
    }

    public function getProductBySku($product, $sku = null)
    {
        if (!$sku) {
            return $product;
        }

        try {
            return $this->productRepository->get($sku);
        } catch (\Exception $e) {
            return $product;
        }
    }
}
