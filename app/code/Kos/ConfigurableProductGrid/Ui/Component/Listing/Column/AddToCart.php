<?php

namespace Kos\ConfigurableProductGrid\Ui\Component\Listing\Column;

class AddToCart extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $block;
    protected $_productloader;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Block\Product\View $block,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->block = $block;
        $this->_productloader = $_productloader;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                $_product = $this->_productloader->create()->load($item['entity_id']);
                $item['qty'] = $this->block->getLayout()->createBlock(\Kos\ConfigurableProductGrid\Block\Actions::class)
                    ->setTemplate('Kos_ConfigurableProductGrid::actions.phtml')
                    ->setAttribute('product', $_product)
                    ->toHtml();
            }
        }

        return $dataSource;
    }
}