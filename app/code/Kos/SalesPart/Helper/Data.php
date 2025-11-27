<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kos\SalesPart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const PATH_REQUEST4QUOTE_KOS_PDF_SIGNATURE = 'bss_request4quote/identity/signature';

    const PATH_REQUEST4QUOTE_KOS_B2B_PAYMENT_CONDITION = 'bss_request4quote/kos_b2b/payment_condition';
    const PATH_REQUEST4QUOTE_KOS_B2B_VALIDITY = 'bss_request4quote/kos_b2b/validity';
    const PATH_REQUEST4QUOTE_KOS_B2B_TERM = 'bss_request4quote/kos_b2b/term';
    const PATH_REQUEST4QUOTE_KOS_B2B_BANK_INFO = 'bss_request4quote/kos_b2b/bank_info';
    const PATH_REQUEST4QUOTE_KOS_B2B_PERSON_NAME = 'bss_request4quote/kos_b2b/person_name';
    const PATH_REQUEST4QUOTE_KOS_B2B_PERSON_TEL = 'bss_request4quote/kos_b2b/person_phone';

    const PATH_REQUEST4QUOTE_KOS_B2C_PAYMENT_CONDITION = 'bss_request4quote/kos_b2c/payment_condition';
    const PATH_REQUEST4QUOTE_KOS_B2C_VALIDITY = 'bss_request4quote/kos_b2c/validity';
    const PATH_REQUEST4QUOTE_KOS_B2C_TERM = 'bss_request4quote/kos_b2c/term';
    const PATH_REQUEST4QUOTE_KOS_B2C_BANK_INFO = 'bss_request4quote/kos_b2c/bank_info';
    const PATH_REQUEST4QUOTE_KOS_B2C_PERSON_NAME = 'bss_request4quote/kos_b2c/person_name';
    const PATH_REQUEST4QUOTE_KOS_B2C_PERSON_TEL = 'bss_request4quote/kos_b2c/person_phone';

    protected $string;

    protected $customerQuote = null;

    protected $addressQuote = null;

    protected $currencyFactory;

    protected $quoteCurrency;

    protected $customerRepositoryInterface;

    protected $addressFactory;

    protected $groupRepository;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        parent::__construct($context);
        $this->string = $string;
        $this->currencyFactory = $currencyFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->addressFactory = $addressFactory;
        $this->groupRepository = $groupRepository;
    }

    public function getCustomerGroupNameById($groupId = 1)
    {
        $customerGroup = $this->groupRepository->getById($groupId);
        return $customerGroup->getCode();
    }

    public function getB2BPerSonName($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2B_PERSON_NAME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getB2BPerSonPhone($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2B_PERSON_TEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getB2CPerSonName($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2C_PERSON_NAME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getB2CPerSonPhone($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2C_PERSON_TEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getB2BPaymentCondition($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2B_PAYMENT_CONDITION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getB2BValidity($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2B_VALIDITY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getB2BTermAndCondition($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2B_TERM,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getB2BBankInfor($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2B_BANK_INFO,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getB2CPaymentCondition($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2C_PAYMENT_CONDITION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getB2CValidity($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2C_VALIDITY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getB2CTermAndCondition($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2C_TERM,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getB2CBankInfor($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_B2C_BANK_INFO,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getSignature($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_KOS_PDF_SIGNATURE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getCustomer($customerId)
    {
        if ($this->customerQuote) {
            return $this->customerQuote;
        }

        try {
            $this->customerQuote = $this->customerRepositoryInterface->getById($customerId);
        } catch (\Exception $e) {
            $this->customerQuote = null;
        }

        return $this->customerQuote;
    }

    public function getCustomerName($customerId)
    {
        $customer = $this->getCustomer($customerId);
        if ($customer) {
            return $customer->getFirstName() . ' ' . $customer->getLastName();
        }

        return '';
    }

    public function getAddressObject($customerId)
    {
        if ($this->addressQuote) {
            return $this->addressQuote;
        }

        $customer = $this->getCustomer($customerId);
        if ($customer) {
            $billingAddressId = $customer->getDefaultBilling();

            if ($billingAddressId) {
                $this->addressQuote = $this->addressFactory->create()->load($billingAddressId);
            }
        }

        return $this->addressQuote;
    }

    public function getCustomerAddress($customerId)
    {
        $address = $this->getAddressObject($customerId);
        if ($address) {
            $streetAddress = $address->getStreet();
            if (is_array($streetAddress)) {
                $streetAddress = implode(', ', $streetAddress);
            }
            
            return $streetAddress
                . ', ' . $address->getData('city')
                . ', ' . $address->getData('region')
                . ' ' . $address->getData('postcode');
        }

        return '';
    }

    public function getCustomerCompany($customerId)
    {
        $address = $this->getAddressObject($customerId);
        if ($address) {
            return $address->getData('company');
        }

        return '';
    }

    public function getCustomerPhone($customerId)
    {
        $address = $this->getAddressObject($customerId);
        if ($address) {
            return $address->getData('telephone');
        }

        return '';
    }

    public function getCustomerVAT($customerId)
    {
        $address = $this->getAddressObject($customerId);
        if ($address) {
            return $address->getData('vat_id');
        }

        return '';
    }

    public function getPerSonName($store = null, $storeName = 'B2B')
    {
        if ($storeName === 'B2B') {
            return $this->getB2BPerSonName($store);
        }

        return $this->getB2CPerSonName($store);
    }

    public function getPerSonPhone($store = null, $storeName = 'B2B')
    {
        if ($storeName === 'B2B') {
            return $this->getB2BPerSonPhone($store);
        }

        return $this->getB2CPerSonPhone($store);
    }

    public function getPaymentCondition($store = null, $storeName = 'B2B')
    {
        if ($storeName === 'B2B') {
            return $this->getB2BPaymentCondition($store);
        }

        return $this->getB2CPaymentCondition($store);
    }

    public function getValidity($store = null, $storeName = 'B2B')
    {
        if ($storeName === 'B2B') {
            return $this->getB2BValidity($store);
        }

        return $this->getB2CValidity($store);
    }

    public function getTermAndCondition($store = null, $storeName = 'B2B')
    {
        if ($storeName === 'B2B') {
            return $this->getB2BTermAndCondition($store);
        }

        return $this->getB2CTermAndCondition($store);
    }

    public function getBankInfor($store = null, $storeName = 'B2B')
    {
        if ($storeName === 'B2B') {
            return $this->getB2BBankInfor($store);
        }

        return $this->getB2CBankInfor($store);
    }

    public function calcValueHeight($value, $length = 30)
    {
        $y = 0;
        $values = explode("\n", $value);
        foreach ($values as $value) {
            if ($value !== '' && trim($value) !== '') {
                $text = [];
                foreach ($this->string->split($value, $length, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $y += 12;
                }
            }
        }
        return $y;
    }

    public function getProductData(\Magento\Catalog\Model\Product $product)
    {
        $items = [];
        $qtyDayToShip = [];
        $attr = $product->getResource()->getAttribute('part_number');
        $items['part_number'] = $product->getData('part_number');
        if ($attr->usesSource()) {
            $idVal = (int)$product->getResource()->getAttributeRawValue($product->getId(), 'part_number', null);
            $optionText = $attr->getSource()->getOptionText($idVal);
            $items['part_number'] = $optionText;
        }

        $attr = $product->getResource()->getAttribute('material');
        if ($attr->usesSource()) {
            $idVal = (int)$product->getResource()->getAttributeRawValue($product->getId(), 'material', null);
            $optionText = $attr->getSource()->getOptionText($idVal);
            $items['material'] = $optionText;
        }

        $attr = $product->getResource()->getAttribute('plating');
        if ($attr->usesSource()) {
            $idVal = (int)$product->getResource()->getAttributeRawValue($product->getId(), 'plating', null);
            $optionText = $attr->getSource()->getOptionText($idVal);
            $items['plating'] = $optionText;
        }

        $attr = $product->getResource()->getAttribute('diameter');
        if ($attr->usesSource()) {
            $idVal = (int)$product->getResource()->getAttributeRawValue($product->getId(), 'diameter', null);
            $optionText = $attr->getSource()->getOptionText($idVal);
            $items['diameter'] = $optionText;
        }

        $attr = $product->getResource()->getAttribute('length');
        if ($attr->usesSource()) {
            $idVal = (int)$product->getResource()->getAttributeRawValue($product->getId(), 'length', null);
            $optionText = $attr->getSource()->getOptionText($idVal);
            $items['length'] = $optionText;
        }

        if ($product->getTierPrice()) {
            $tierPrices = $product->getTierPrice();
            $countTier = count($tierPrices);
            $qty_old = '';

            for ($i = 0; $i < $countTier; $i++) {
                if (!empty($tierPrices[$i + 1])) {
                    $qty_old = (int)$tierPrices[$i]['price_qty'];
                    $qtyDayToShip[] = [
                        'from' => $qty_old,
                        'to' => (int)$tierPrices[$i + 1]['price_qty'],
                        'day_to_ship' => $tierPrices[$i]['day_to_ship']
                    ];
                    $qty_old = (int)$tierPrices[$i + 1]['price_qty'];
                } else {
                    $qtyDayToShip[] = [
                        'from' => $qty_old,
                        'to' => '>',
                        'day_to_ship' => $tierPrices[$i]['day_to_ship']
                    ];
                }
            }
        } else {
            $qtyDayToShip[] = [
                'from' => 1,
                'to' => '>',
                'day_to_ship' => $product->getResource()->getAttributeRawValue($product->getId(), 'day_to_ship', null)
            ];
        }

        $items['day_to_ship'] = $qtyDayToShip;

        return $items;
    }

    public function formatPriceTxt($price, $order)
    {
        return $this->getQuoteCurrency($order)->formatTxt($price);
    }

    public function getQuoteCurrency($order)
    {
        if ($this->quoteCurrency === null) {
            $this->quoteCurrency = $this->currencyFactory->create();
            $this->quoteCurrency->load($order->getQuoteCurrencyCode());
        }
        return $this->quoteCurrency;
    }
}