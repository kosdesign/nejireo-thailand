<?php
namespace Kos\CatalogExtend\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $tableName = $setup->getTable('catalog_product_entity_tier_price');
            $this->addAskPriceColumn($setup, $tableName);
        }

        $setup->endSetup();
    }

    private function addAskPriceColumn(SchemaSetupInterface $installer, $tableName)
    {
        $installer->getConnection()->addColumn(
            $tableName,
            'ask_price',
            [
                'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'LENGTH' => null,
                'COMMENT' => 'Ask Price'
            ]
        );
    }
}