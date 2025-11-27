<?php
namespace Kos\SalesPart\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$context->getVersion() ||  version_compare($context->getVersion(), '1.0.2', '<')) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote_extension'),
                    'pdf_file',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 64,
                        'default' => '',
                        'nullable' => false,
                        'comment' => 'PDF File'
                    ]
                );
        }

        $setup->endSetup();
    }
}
