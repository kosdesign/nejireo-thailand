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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Setup;

use Bss\QuoteExtension\Helper\Data as HelperData;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

/**
 * Class UpgradeSchema
 *
 * @package Bss\QuoteExtension\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    protected $helperData;

    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws Zend_Db_Exception
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.4', '<=')) {
            $columnExist = $installer->getConnection()->tableColumnExists(
                $installer->getTable('quote_extension'),
                'old_quote'
            );
            if (!$columnExist) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('quote_extension'),
                    'old_quote',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Old Quote'
                    ]
                );
            }
        }
        if (version_compare($context->getVersion(), '1.0.5', '<=')) {
            $installer->getConnection()->modifyColumn(
                $installer->getTable('quote_extension'),
                'updated_at',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT_UPDATE,
                    'comment' => 'Updated At'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.8', '<=')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('quote_extension'),
                'is_admin_submitted',
                [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Is Submitted'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $this->addColumnSubUserId($installer);
            $this->addColumnUserId($installer);
        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $this->addColumnNotMoveCheckout($installer);
        }

        //Update Quote expiry Email checker
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $columnExist = $installer->getConnection()->tableColumnExists(
                $installer->getTable('quote_extension'),
                'expiry_email_sent'
            );
            if (!$columnExist) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('quote_extension'),
                    'expiry_email_sent',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'nullable' => true,
                        'default' => 0,
                        'comment' => 'Expiry Email Sent'
                    ]
                );
            }
        }

        $installer->endSetup();
    }

    /**
     * Add column sub_user_id when company account install
     *
     * @param SchemaSetupInterface $installer
     */
    public function addColumnSubUserId($installer)
    {
        if ($this->helperData->isInstallCompanyAccount()) {
            $tableName = $installer->getTable('quote_extension');
            if (!$installer->getConnection()->tableColumnExists($installer->getTable($tableName), "sub_user_id")) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('quote_extension'),
                    'sub_user_id',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'nullable' => true,
                        'comment' => 'Sub User Id'
                    ]
                );
            }
        }
    }
    /**
     * Add column user_id when module sales_rep install
     *
     * @param SchemaSetupInterface $installer
     */
    public function addColumnUserId($installer)
    {
        if ($this->helperData->isInstallSalesRep()) {
            $tableName = $installer->getTable('quote_extension');
            if (!$installer->getConnection()->tableColumnExists($installer->getTable($tableName), "user_id")) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('quote_extension'),
                    'user_id',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'nullable' => true,
                        'comment' => 'User Id'
                    ]
                );
            }
        }
    }

    /**
     * Add column not_move_checkout in table quote_extension
     *
     * @param SchemaSetupInterface $installer
     */
    public function addColumnNotMoveCheckout($installer) {
        $installer->getConnection()->addColumn(
            $installer->getTable('quote_extension'),
            'move_checkout',
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => true,
                'comment' => 'Move Checkout'
            ]
        );
    }
}
