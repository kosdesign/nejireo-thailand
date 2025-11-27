<?php
namespace Kos\UploadDocument\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetFile extends \Magento\Framework\View\Element\Template
{

    const UPLOAD_DIR = 'CustomUploadDir';
    const CUSTOM_UPLOAD_FILE = 'upload_file/group_a/custom_upload_file';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        Template\Context $context,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function _getUploadDir(): string
    {

        $fileName = $this->scopeConfig->getValue(self::CUSTOM_UPLOAD_FILE);
        $currentStore = $this->_storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl.self::UPLOAD_DIR."/".$fileName;
    }

}
