<?php

namespace Kos\ConfigurableProductGrid\Model\Option;

class Material implements \Magento\Framework\Option\ArrayInterface
{
    protected $helper;

    public function __construct(
        \Kos\ConfigurableProductGrid\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }

    public function toOptionArray()
    {
        return $this->helper->getOptionsArray('material');
    }
}