<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Source\PageType;

use Magento\Store\Model\Store;
use Magento\Framework\App\State;

class Cms extends Emulated
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    private $pageCollection;

    public function __construct(
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageCollectionFactory,
        \Magento\Framework\Url $url,
        \Magento\Store\Model\App\Emulation $appEmulation,
        State $appState,
        $isMultistoreMode = false,
        array $stores = [],
        \Closure $filterCollection = null
    ) {
        parent::__construct($url, $appEmulation, $appState, $isMultistoreMode, $stores, $filterCollection);

        $this->pageCollection = $pageCollectionFactory->create();
        $this->pageCollection->addFieldToFilter('is_active', true);
    }

    protected function getEntityCollection($storeId)
    {
        return $this->pageCollection;
    }

    /**
     * @param \Magento\Cms\Model\Page $entity
     * @param                         $storeId
     *
     * @return bool|string
     */
    protected function getUrl($entity, $storeId)
    {
        if ($this->isMultistoreMode
            && !in_array(Store::DEFAULT_STORE_ID, $entity->getStores())
            && !in_array($storeId, $entity->getStores())
        ) {
            // Page is not visible for this store
            return false;
        } else {
            return $entity->getIdentifier() . '/';
        }
    }
}
