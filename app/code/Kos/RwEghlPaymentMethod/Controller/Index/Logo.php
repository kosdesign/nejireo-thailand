<?php

namespace Kos\RwEghlPaymentMethod\Controller\Index;
class Logo extends \Eghl\PaymentMethod\Controller\Index\Logo
{
    public function execute()
    {
        $assetRepo = $this->_objectManager->get('Magento\Framework\View\Asset\Repository');
        $imgPath = $assetRepo->getUrl("Eghl_PaymentMethod::img/eghl_logo.png");
        $this->response->setHeader('content-type', 'image/png', $overwriteExisting = true);
        $this->response->setContent(file_get_contents($imgPath));
    }
}

?>