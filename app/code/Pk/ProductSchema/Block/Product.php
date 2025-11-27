<?php
namespace Pk\ProductSchema\Block;
class Product extends \Magento\Framework\View\Element\Template
{
    protected $_registry;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    public function getImageUrl(){
        try
        {
            $product = $this->productFactory->create()->load($this->getCurrentProduct()->getId());
        }
        catch (\Exception $e)
        {
            return 'Data not found';
        }
        $url = $this->imageHelper->init($product, 'product_page_image_large')->getUrl();
        return $url;
    }

}
