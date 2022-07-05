<?php

declare(strict_types=1);

namespace MageSuite\Media\Setup\Patch\Data;

class AddUseDefaultMediaAttribute implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    protected \Magento\Eav\Setup\EavSetup $eavSetup;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->eavSetup = $eavSetupFactory->create(['setup' => $moduleDataSetup]);
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'use_default_media',
            [
                'type' => 'int',
                'backend' => '',
                'label' => 'Use Default Value',
                'input' => 'checkbox',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'visible' => true,
                'required' => false,
                'default' => '0',
                'frontend' => '',
                'unique' => false,
                'note' => 'After checking the checkbox and saving the form, the store value will be removed.',
                'group' => 'Default Images And Videos'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
