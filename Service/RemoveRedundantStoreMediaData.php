<?php

declare(strict_types=1);

namespace MageSuite\Media\Service;

class RemoveRedundantStoreMediaData
{
    public function __construct(
        protected \MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData $redundantStoreMediaData,
        protected \Magento\Framework\DataObjectFactory $dataObjectFactory,
        protected int $batchSize = 1000
    ) {
    }

    public function execute(?array $productIds = null, ?int $storeId = null, bool $dryRun = false): \Magento\Framework\DataObject
    {
        $totals = $this->dataObjectFactory->create(['data' => [
            \MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData::GALLERY_ROWS => 0,
            \MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData::ROLE_VALUES => 0,
        ]]);

        if ($productIds !== null) {
            $this->addToTotals($totals, $this->processBatch($productIds, $storeId, $dryRun));

            return $totals;
        }

        $batchProductIds = $this->redundantStoreMediaData->getProductIds(0, $this->batchSize);

        while (!empty($batchProductIds)) {
            $this->addToTotals($totals, $this->processBatch($batchProductIds, $storeId, $dryRun));
            $batchProductIds = $this->redundantStoreMediaData->getProductIds(max($batchProductIds), $this->batchSize);
        }

        return $totals;
    }

    protected function processBatch(array $productIds, ?int $storeId, bool $dryRun): \Magento\Framework\DataObject
    {
        if ($dryRun) {
            return $this->redundantStoreMediaData->countRedundantData($productIds, $storeId);
        }

        return $this->redundantStoreMediaData->removeRedundantData($productIds, $storeId);
    }

    protected function addToTotals(\Magento\Framework\DataObject $totals, \Magento\Framework\DataObject $result): void
    {
        foreach ($result->getData() as $key => $count) {
            $totals->setData($key, $totals->getDataByKey($key) + $count);
        }
    }
}
