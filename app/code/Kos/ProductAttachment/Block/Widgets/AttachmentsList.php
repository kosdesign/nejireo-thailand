<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kos\ProductAttachment\Block\Widgets;

use Amasty\ProductAttachment\Model\File\FileScope\FileScopeDataProvider;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Amasty\ProductAttachment\Model\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;

class AttachmentsList extends \Amasty\ProductAttachment\Block\Widgets\AttachmentsList
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var FileScopeDataProvider
     */
    protected $fileScopeDataProvider;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Url
     */
    protected $customerUrl;

    public function __construct(
        FileScopeDataProvider $fileScopeDataProvider,
        ConfigProvider $configProvider,
        Registry $registry,
        Template\Context $context,
        Session $session,
        Url $customerUrl,
        array $data = []
    )
    {
        parent::__construct($fileScopeDataProvider, $configProvider, $registry, $context, $data);
        $this->session = $session;
        $this->customerUrl = $customerUrl;
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
}
