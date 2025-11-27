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
 * @package    Bss_CustomQuote
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Kos\CustomBssQuote\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @package Kos\CustomBssQuote\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $installer->getConnection()
            ->addColumn(
                $installer->getTable('cataloginventory_stock_item'),
                'bss_max_qty_quote',
                [
                    'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => 1000,
                    'comment' => 'Bss Maximum Qty Quote',
                ]
            );
        $installer->getConnection()
            ->addColumn(
                $installer->getTable('cataloginventory_stock_item'),
                'use_config_bss_maximum_qty_quote',
                [
                    'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Bss Use Config Maximum Qty Quote',
                ]
            );
        $installer->endSetup();
    }
}
