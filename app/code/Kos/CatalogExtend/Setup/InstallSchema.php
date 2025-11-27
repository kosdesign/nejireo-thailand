<?php
namespace Kos\CatalogExtend\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $tableName = $setup->getTable('catalog_product_entity_tier_price');
        $this->updateTierPriceTable($setup, $tableName);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param $tableName
     */
    private function updateTierPriceTable(SchemaSetupInterface $installer, $tableName)
    {
        $installer->getConnection()->addColumn(
            $tableName,
            'day_to_ship',
            [
                'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'LENGTH' => null,
                'COMMENT' => 'Day To Ship'
            ]
        );
    }
}
