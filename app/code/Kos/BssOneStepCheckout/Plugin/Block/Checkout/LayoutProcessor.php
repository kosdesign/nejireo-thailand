<?php

namespace Kos\BssOneStepCheckout\Plugin\Block\Checkout;

use Bss\OneStepCheckout\Helper\Config;
use Bss\OneStepCheckout\Helper\Data;

/**
 * Class LayoutProcessor
 *
 * @package Kos\BssOneStepCheckout\Plugin\Block\Checkout\Checkout
 */
class LayoutProcessor
{
    /**
     * One step checkout helper
     *
     * @var Config
     */
    protected $configHelper;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * LayoutProcessor constructor.
     * @param Config $configHelper
     * @param Data $dataHelper
     */
    public function __construct(
        Config $configHelper,
        Data $dataHelper
    )
    {
        $this->configHelper = $configHelper;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    )
    {
        $jsLayout = $this->billingSortOrder($jsLayout);
        $jsLayout = $this->discountCode($jsLayout);
        $jsLayout['components']['checkout']['children']['authentication']['componentDisabled'] = true;
        return $jsLayout;
    }

    /**
     * @param $jsLayout
     * @return mixed
     */
    protected function billingSortOrder($jsLayout)
    {
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['payments-list']['children']
        ['billing-address-form-shared']['children']['form-fields']['children']['vat_id']['sortOrder'] = 65;
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['payments-list']['children']
        ['billing-address-form-shared']['children']['form-fields']['children']['country_id']['sortOrder'] = 75;
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['payments-list']['children']
        ['billing-address-form-shared']['children']['form-fields']['children']['region_id']['sortOrder'] = 80;
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['payments-list']['children']
        ['billing-address-form-shared']['children']['form-fields']['children']['city']['sortOrder'] = 85;

        return $jsLayout;
    }

    /**
     * @param $jsLayout
     * @return mixed
     */
    protected function discountCode($jsLayout)
    {
        if ($this->configHelper->isDisplayField('enable_discount_code')) {
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary_totals'] =
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals'];
            $jsLayout['components']['checkout']['children']['sidebar']['children']['discount']['displayArea'] = 'summary-bottom';
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary_totals']['displayArea'] = 'summary-bottom';
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['componentDisabled'] = true;
            $jsLayout['components']['checkout']['children']['sidebar']['children']['discount']
            ['template'] = 'Kos_BssOneStepCheckout/payment/discount';
            $jsLayout['components']['checkout']['children']['sidebar']['children']['discount']['sortOrder'] = 10;
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary_totals']['children']['discount']['sortOrder'] = 30;
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary_totals']['children']['tax']['sortOrder'] = 40;
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary_totals']['children']['weee']['sortOrder'] = 50;
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary_totals']['children']['vertex-messages']['sortOrder'] = 60;
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary_totals']['children']['gift_wrap']['sortOrder'] = 70;
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary_totals']['children']['grand-total']['sortOrder'] = 220;
            if(!empty($jsLayout['config']) && ($jsLayout['config']['additionalClasses'] == 'quoteextension_checkout')) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['discount']['componentDisabled'] = true;
            }
        }

        return $jsLayout;
    }
}