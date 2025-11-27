<?php
namespace Kos\Design\Block\Category;
use Magento\Framework\App\ObjectManager;
class Navigation extends \Magento\Framework\View\Element\Template
{        

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Helper\Image $helperImage
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,  
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $helperImage,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->categoryHelper = $categoryHelper;
        $this->categoryRepository = $categoryRepository;
        $this->productFactory = $productFactory;
        $this->helperImage = $helperImage;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }
    
    public function getCategoryLevelOne()
    {        
        return $this->categoryHelper->getStoreCategories();
    }
    
    public function getCategoryById($categoryId='')
    {        
	$objectManager = ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $current_store =  $storeManager->getStore()->getId();
        return $this->categoryRepository->get($categoryId,$current_store);
    }
    
    public function getProductById($productId='')
    {        
        return $this->productFactory->create()->load($productId);
    }

    public function getUrlImage($product){
        return $this->helperImage->init($product, 'product_base_image')
            ->constrainOnly(TRUE)
            ->keepAspectRatio(TRUE)
            ->keepTransparency(TRUE)
            ->keepFrame(FALSE)
            ->resize(150, 150)->getUrl();
    }
    
    public function getProductsPosition($category = array())
    {        
        $products = array();
        foreach ($category->getProductsPosition() as $key => $postiton) {
            if ($postiton >= 1 && $postiton <= 6) {
                $products[$postiton] = $key;
            }
        }
        ksort($products);
        return $products;
    }
    
    public function getCategory()
    {        
        return $this->registry->registry('current_category');
    }
}
