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
namespace Bss\QuoteExtension\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 *
 * @package Bss\QuoteExtension\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Install Construct
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $quoteTable = $installer->getTable('quote');
        $installer->getConnection()->addColumn(
            $quoteTable,
            'quote_extension',
            [
                'type'     => Table::TYPE_INTEGER,
                'length'   => null,
                'nullable' => true,
                'comment'  => 'Quote Extension'
            ]
        );

        $installer->getConnection()->addColumn(
            $quoteTable,
            'quote_shipping_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'nullable' => false,
                'length' => '12,4',
                'comment' => 'Quote Shipping Price'
            ]
        );

        $installer->getConnection()->modifyColumn(
            $quoteTable,
            'customer_note',
            [
                'type' => Table::TYPE_TEXT
            ]
        );

        /**
         * Create table 'quote_extension'
         */
        $this->createQuoteExtensionTable($setup, $installer);

        /**
         * Create table 'quote_extension_item'
         */
        $this->createQuoteExtensionItemTable($installer);

        /**
         * Create table 'quote_extension_comment_version'
         */
        $this->createQuoteExtensionCommentVersionTable($installer);

        $installer->endSetup();
    }

    /**
     * Create table quote_extension
     *
     * @param SchemaSetupInterface $setup
     * @param SchemaSetupInterface $installer
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function createQuoteExtensionTable($setup, $installer)
    {
        $setup->getConnection()->dropTable($setup->getTable('quote_extension'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('quote_extension')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'quote_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'unique' => true],
            'Quote Id'
        )->addColumn(
            'backend_quote_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Backend Quote Id'
        )->addColumn(
            'target_quote',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Target Quote'
        )->addColumn(
            'increment_id',
            Table::TYPE_TEXT,
            32,
            [],
            'Increment Id'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Customer Id'
        )->addColumn(
            'status',
            Table::TYPE_TEXT,
            32,
            ['unsigned' => true],
            'Status'
        )->addColumn(
            'token',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Token'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Email'
        )->addColumn(
            'email_sent',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Email Sent'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Updated At'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'expiry',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Expiry'
        )->addColumn(
            'expiry_email_sent',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Expiry Email Sent'
        )->addColumn(
            'version',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false],
            'Version'
        )->addIndex(
            $installer->getIdxName('quote_extension', ['entity_id']),
            ['entity_id']
        )->addIndex(
            $installer->getIdxName(
                'quote_extension',
                ['increment_id', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['increment_id', 'store_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Quote Extension Table'
        );
        $installer->getConnection()->createTable($table);
    }

    /**
     * Create table quote_extension_item
     *
     * @param SchemaSetupInterface $installer
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function createQuoteExtensionItemTable($installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('quote_extension_item')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'item_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'unique' => true],
            'Item ID'
        )->addColumn(
            'comment',
            Table::TYPE_TEXT,
            '64k',
            ['identity' => false, 'unsigned' => false, 'nullable' => true],
            'Comment'
        )->addForeignKey(
            $installer->getFkName('quote_extension_item', 'item_id', 'quote_item', 'item_id'),
            'item_id',
            $installer->getTable('quote_item'),
            'item_id',
            Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName('quote_extension_item', ['id']),
            ['id']
        )->setComment(
            'Quote Extension Items'
        );
        $installer->getConnection()->createTable($table);
    }

    /**
     * Create table quote_extension_comment_version
     *
     * @param SchemaSetupInterface $installer
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function createQuoteExtensionCommentVersionTable($installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('quote_extension_comment_version')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'quote_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false],
            'Quote ID'
        )->addColumn(
            'version',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false],
            'Version'
        )->addColumn(
            'status',
            Table::TYPE_TEXT,
            32,
            ['unsigned' => true],
            'Status'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'comment',
            Table::TYPE_TEXT,
            '64k',
            ['identity' => false, 'unsigned' => false, 'nullable' => true],
            'Comment'
        )->addColumn(
            'area_log',
            Table::TYPE_TEXT,
            32,
            ['unsigned' => true],
            'Area Log'
        )->addColumn(
            'log',
            Table::TYPE_TEXT,
            '64k',
            ['identity' => false, 'unsigned' => false, 'nullable' => true],
            'Log Version Quote'
        )->addIndex(
            $installer->getIdxName('quote_extension_comment_version', ['id']),
            ['id']
        )->setComment(
            'Quote Extension Comment Version'
        );
        $installer->getConnection()->createTable($table);
    }
}
