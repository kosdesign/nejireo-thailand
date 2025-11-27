<?php
namespace Kos\CustomAttributes\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Setup\CategorySetupFactory;

class InstallData implements InstallDataInterface
{

    private $attributeSetFactory;
    private $categorySetupFactory;

    public function __construct(
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $attributeSet = $this->attributeSetFactory->create();
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        $data = [
            [
                'attribute_set_name' => 'HN Config 1', //attribute set name
                'entity_type_id' => $entityTypeId,
                'sort_order' => 50,
            ],
            [
                'attribute_set_name' => 'HN Simple 1', //attribute set name
                'entity_type_id' => $entityTypeId,
                'sort_order' => 50,
            ]
        ];
        foreach ($data as $attr) {
            $attributeSet->setData($attr);
            $attributeSet->validate();
            $attributeSet->save();
            $attributeSet->initFromSkeleton($attributeSetId)->save();
        }
    }
}
