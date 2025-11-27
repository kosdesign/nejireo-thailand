<?php
namespace Kos\CatalogExtend\Setup;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * UpgradeData constructor.
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     *
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.2', '<')) {
            $content = <<<EOT
<div class="card-contact-support">
    <div class="card-contact">
        <a href="/contact_form.php">
            <span class="icon"><i class="icomoon-chat">&nbsp;</i></span>
            <span class="text">Contact Us</span>
        </a>
    </div>
    <div class="card-support">
        <div class="card-title d-none d-lg-block text-body-18 color-grey-9c font-bold text-center"> Customer Support</div>
        <div class="card-list color-grey-92">
            <div class="card-item">
                <div class="icon text-body-16 mb-2"><i class="icomoon-mail">&nbsp;</i></div>
                <div class="text">support@hanshinneji.com</div>
            </div>
            <div class="card-item">
                <div class="icon text-body-18 mb-2"><i class="icomoon-phone">&nbsp;</i></div>
                <div class="text">02 425 5256 </div>
            </div>
        </div>
    </div>
</div>
EOT;
            $cmsBlockData = [
                'title' => 'Card Support',
                'identifier' => 'card-support',
                'content' => $content,
                'is_active' => 1,
                'stores' => [0]
            ];

            $this->blockFactory->create()->setData($cmsBlockData)->save();
        }

        $setup->endSetup();
    }
}
