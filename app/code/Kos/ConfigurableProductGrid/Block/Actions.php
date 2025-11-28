<?php

namespace Kos\ConfigurableProductGrid\Block;

use Magento\Framework\View\Element\Template\Context;

class Actions extends \Magento\Framework\View\Element\Template
{
    protected $block;
    protected $helperConfig;
    protected $formKey;
    private $stockRegistry;
    protected $registry;
    protected $catalogProductHelper;
    protected $request;
    protected $productTypeInstance;
    protected $productloader;
    protected $session;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

        /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    public function __construct(
        Context $context,
        \Magento\Catalog\Block\Product\View $block,
        \Bss\QuoteExtension\Helper\Admin\ConfigShow $helperConfig,
        \Bss\QuoteExtension\Helper\Data $helper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->block = $block;
        $this->helperConfig = $helperConfig;
        $this->helper = $helper;
        $this->formKey = $formKey;
        $this->stockRegistry = $stockRegistry;
        $this->registry = $registry;
        $this->catalogProductHelper = $catalogProductHelper;
        $this->request = $request;
        $this->productTypeInstance = $productTypeInstance;
        $this->productloader = $productloader;
        $this->session = $session;
        $this->customerUrl = $customerUrl;
        $this->resourceConnection = $resourceConnection;
    }


    public function getProduct()
    {
        return $this->getData('product');
    }

    public function getSubmitUrl()
    {
        return $this->block->escapeUrl($this->block->getSubmitUrl($this->getProductParent()));
    }

    public function getProductDefaultQty()
    {
        return $this->block->getProductDefaultQty($this->getProduct());
    }

    public function getQuantityValidators()
    {
        $stockItem = $this->stockRegistry->getStockItem(
            $this->getProduct()->getId(),
            $this->getProduct()->getStore()->getWebsiteId()
        );

        $validators = [];
        $validators['required-number'] = true;
        $params['minAllowed'] = (float)$stockItem->getMinSaleQty();
        if ($stockItem->getMaxSaleQty()) {
            $params['maxAllowed'] = (float)$stockItem->getMaxSaleQty();
        }
        if ($stockItem->getQtyIncrements() > 0) {
            $params['qtyIncrements'] = (float)$stockItem->getQtyIncrements();
        }
        $validators['validate-item-quantity'] = $params;
        return $validators;
    }

    public function getButtonQuoteTitle()
    {
        //$buttonTitle = $this->helperConfig->getProductPageText();
        //($buttonTitle) ? $buttonTitle = $buttonTitle : __('Add to Quote');
	$buttonTitle = __('Add to Quote');
        return $buttonTitle;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function validateQuantity()
    {
        return $this->helper->validateQuantity();
    }

    public function getAttribute($id) {
    	return $this->getProduct()->getData($id);
    }

    public function getSuperAtribute()
    {
    	$product = $this->getProductParent();
    	return $this->productTypeInstance->getConfigurableAttributesAsArray($product);

    }

    public function getProductParent()
    {
    	$product = $this->registry->registry('current_product');
    	if (!$product) {
            $id = $this->request->getParam('id');
            $product = $this->productloader->create()->load($id);
        }

        return $product;
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->session->isLoggedIn();
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->customerUrl->getLoginUrl();
    }


    /**
     * Check if product has ask_price enabled in tier price
     *
     * @return bool
     */
    public function hasAskPrice()
    {
        $product = $this->getProduct();
        if (!$product || !$product->getId()) {
            return false;
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalog_product_entity_tier_price');

        $select = $connection->select()
            ->from($tableName, ['ask_price'])
            ->where('entity_id = ?', $product->getId())
            ->where('ask_price = ?', '1')
            ->limit(1);

        $result = $connection->fetchOne($select);

        return $result == '1';
    }
}
