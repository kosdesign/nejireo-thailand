<?php
namespace Kos\CustomAttributes\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * CreateUrlAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $attributeGroup = 'HN Config';
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'material',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'int',
                    'label' => 'Material',
                    'input' => 'select',
                    'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'apply_to' => '',
                    'option' => [
                        'values' => [
                            'SCM435',
                            'SCM436'
                        ]
                    ]
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'plating',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'int',
                    'label' => 'Plating',
                    'input' => 'select',
                    'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'option' => [
                        'values' => [
                            'BLACK OXIDE',
                            'NONE',
                            'TRIVALENT CHROMATE'
                        ]
                    ]
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'diameter',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'int',
                    'label' => 'Diameter',
                    'input' => 'select',
                    'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'option' => [
                        'values' => [
                            '2',
                            '3',
                            '4',
                            '5',
                            '6',
                            '8',
                            '10',
                            '12',
                            '14',
                            '16',
                            '18',
                            '20'
                        ]
                    ]
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'length',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'int',
                    'label' => 'Length',
                    'input' => 'select',
                    'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'option' => [
                        'values' => [
                            '1',
                            '2'
                        ]
                    ]
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'part_no',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'varchar',
                    'label' => 'Part No.',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'material_parent',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'varchar',
                    'label' => 'Material (Basic info attributes for parent product)',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'thread_type',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'varchar',
                    'label' => 'Thread Type',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'standard',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'varchar',
                    'label' => 'Standard',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'surface_treatment',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'varchar',
                    'label' => 'Surface Treatment',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'strength',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'varchar',
                    'label' => 'Strength',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'remark',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'varchar',
                    'label' => 'Remark',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $attributeGroup = 'HN Config';
            $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'part_no');

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'part_number',
                [
                    'group' => $attributeGroup,
                    'attribute_set' => 'HN Config 1',
                    'type' => 'varchar',
                    'label' => 'Part Number',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );
        }

        $setup->endSetup();
    }
}
