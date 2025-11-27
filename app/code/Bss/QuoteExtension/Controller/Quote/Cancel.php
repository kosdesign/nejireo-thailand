<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Model\Config\Source\Status;

/**
 * Class Cancel
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Cancel extends \Bss\QuoteExtension\Controller\Quote
{
    /**
     * Excute Function
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $quoteId = (int)$this->getRequest()->getParam('quote');

        $this->manageQuote->load($quoteId);

        if ($this->manageQuote->getId()) {
            try {
                $this->manageQuote->setData('status', Status::STATE_CANCELED);
                $this->manageQuote->save();
                $this->messageManager->addSuccessMessage(__('The quote has been cancelled!'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__("Can't cancel this quote!"));
            }
        } else {
            $this->messageManager->addErrorMessage(__("Can't cancel this quote!"));
        }
        $resultReidrect = $this->resultRedirectFactory->create();
        return $resultReidrect->setPath(
            'quoteextension/quote/view',
            ['quote_id' => $quoteId]
        );
    }
}
