<?php
namespace Kos\CustomBssQuote\Setup;
 
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
 
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;
 
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory
    )
    {
        $this->quoteSetupFactory = $quoteSetupFactory;
    }
 
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
 
        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
 
        $attributeOptions = [
            'type'     => Table::TYPE_TEXT,
            'visible'  => true,
            'required' => false
        ];

        $attributeOptionsChange = [
            'type'     => Table::TYPE_INTEGER,
            'visible'  => true,
            'required' => false
        ];

        $quoteSetup->addAttribute('quote_item', 'day_to_ship', $attributeOptions);
        $quoteSetup->addAttribute('quote_item', 'change_day_to_ship', $attributeOptionsChange);
    }
}