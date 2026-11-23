<?php

declare(strict_types=1);

namespace MageSuite\Media\Plugin\Catalog\Model\Product;

class RemoveRedundantMediaStoreData
{
    public function __construct(
        protected \MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData $redundantStoreMediaData,
        protected \MageSuite\Media\Helper\Configuration $configuration
    ) {
    }

    public function afterAfterSave(
        \Magento\Catalog\Api\Data\ProductInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ): \Magento\Catalog\Api\Data\ProductInterface {
        $storeId = (int)$product->getStoreId();

        if ($storeId === \Magento\Store\Model\Store::DEFAULT_STORE_ID || !$product->getId() || !$this->configuration->isRemovingRedundantStoreDataOnSaveEnabled()) {
            return $product;
        }

        $this->redundantStoreMediaData->removeRedundantData([(int)$product->getId()], $storeId);

        return $product;
    }
}
