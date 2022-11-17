<?php

declare(strict_types=1);

namespace MageSuite\Media\Setup\Patch\Data;

class RemoveUseDefaultImagesAndVideosGroup implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    protected const ATTRIBUTE_GROUP_CODE = 'default-images-and-videos';

    protected \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory;

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
    ) {
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $groupCollection = $this->groupCollectionFactory->create()
            ->addFieldToFilter('attribute_group_code', self::ATTRIBUTE_GROUP_CODE);
        $groupCollection->walk('delete');
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            \MageSuite\Media\Setup\Patch\Data\AddUseDefaultMediaAttribute::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
