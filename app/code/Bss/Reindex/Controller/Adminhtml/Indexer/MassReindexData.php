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
 * @category   BSS
 * @package    Bss_Reindex
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Reindex\Controller\Adminhtml\Indexer;

use Magento\Backend\App\Action;

class MassReindexData extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $registry;

    /**
     * @var \Bss\Reindex\Helper\Data
     */
    protected $helper;

    /**
     * MassReindexData constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\Indexer\IndexerRegistry $registry
     * @param \Bss\Reindex\Helper\Data $helper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Indexer\IndexerRegistry $registry,
        \Bss\Reindex\Helper\Data $helper
    )
    {
        parent::__construct($context);
        $this->registry = $registry;
        $this->helper = $helper;
    }

    protected function _isAllowed()
    {
        if ($this->_request->getActionName() == 'massReindexData') {
            return $this->_authorization->isAllowed('Bss_Reindex::reindexdata')
                && $this->helper->isCoreModuleEnabled();
        }
        return false;
    }

    public function execute()
    {
 	$indexerIds = $this->getRequest()->getParam('indexer_ids');
        if (!is_array($indexerIds)) {
            $this->messageManager->addError(__('Please select indexers.'));
        } else {
            $startTime = microtime(true);
            foreach ($indexerIds as $indexerId) {
                try {
                    $indexer = $this->registry->get($indexerId);
                    
                    // Reset the indexer status to "Update by Schedule" for cron updates
                    $indexer->setScheduled(true);
                    
                    // Reset the indexer state (marks it as invalid so cron will process it)
                    $indexer->invalidate();
                    
                    $resultTime = microtime(true) - $startTime;
                    $this->messageManager->addSuccess(
                        '<div class="bss-reindex-info">' . $indexer->getTitle() . ' index has been reset for cron update successfully in ' . gmdate('H:i:s', $resultTime) . '</div>'
                    );
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError(
                        $indexer->getTitle() . ' indexer reset process error: ' . $e->getMessage()
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addException(
                        $e,
                        __("We couldn't reset indexer data because of an error.")
                    );
                }
            }
            $this->messageManager->addSuccess(
                __('%1 indexer(s) have been reset for cron update successfully <a href="javascript:void(0)" class="bss-reindex-show">Show detail</a>', count($indexerIds))
            );
        }
        $this->_redirect('indexer/indexer/list');

    }
}
