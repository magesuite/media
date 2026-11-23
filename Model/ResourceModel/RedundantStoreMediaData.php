<?php

declare(strict_types=1);

namespace MageSuite\Media\Model\ResourceModel;

class RedundantStoreMediaData
{
    public const GALLERY_ROWS = 'gallery_rows';
    public const ROLE_VALUES = 'role_values';

    protected const GALLERY_VALUE_COLUMNS = ['label', 'position', 'disabled'];
    protected const GALLERY_VALUE_VIDEO_TABLE = 'catalog_product_entity_media_gallery_value_video';

    protected ?array $mediaImageAttributeIdsByTable = null;

    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        protected \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        protected \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
    }

    public function getProductIds(int $lastProductId, int $batchSize): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('catalog_product_entity'), 'entity_id')
            ->where('entity_id > ?', $lastProductId)
            ->order('entity_id ASC')
            ->limit($batchSize);

        return array_map('intval', $connection->fetchCol($select));
    }

    public function countRedundantData(array $productIds, ?int $storeId = null): \Magento\Framework\DataObject
    {
        $result = $this->dataObjectFactory->create();

        foreach ($this->getRedundantDataSelects($productIds, $storeId) as $resultKey => $selects) {
            $result->setData($resultKey, array_sum(array_map([$this, 'countRows'], $selects)));
        }

        return $result;
    }

    public function removeRedundantData(array $productIds, ?int $storeId = null): \Magento\Framework\DataObject
    {
        $result = $this->dataObjectFactory->create();

        foreach ($this->getRedundantDataSelects($productIds, $storeId) as $resultKey => $selects) {
            $result->setData($resultKey, array_sum(array_map([$this, 'deleteRows'], $selects)));
        }

        return $result;
    }

    protected function getRedundantDataSelects(array $productIds, ?int $storeId): array
    {
        $galleryValueTable = $this->resourceConnection->getTableName(\Magento\Catalog\Model\ResourceModel\Product\Gallery::GALLERY_VALUE_TABLE);
        $gallerySelect = $this->addFilters($this->getStoreValueSelect($galleryValueTable, 'value_id'), $productIds, $storeId);

        foreach (self::GALLERY_VALUE_COLUMNS as $column) {
            $gallerySelect->where(sprintf('store_value.%1$s IS NULL OR BINARY store_value.%1$s <=> BINARY default_value.%1$s', $column));
        }

        $this->excludeRowsWithStoreVideoData($gallerySelect);
        $roleSelects = [];

        foreach ($this->getMediaImageAttributeIdsByTable() as $table => $attributeIds) {
            $roleSelect = $this->addFilters($this->getStoreValueSelect($table, 'attribute_id'), $productIds, $storeId);
            $roleSelect->where('store_value.attribute_id IN (?)', $attributeIds);
            $roleSelect->where('BINARY store_value.value <=> BINARY default_value.value');
            $roleSelects[] = $roleSelect;
        }

        return [self::GALLERY_ROWS => [$gallerySelect], self::ROLE_VALUES => $roleSelects];
    }

    protected function excludeRowsWithStoreVideoData(\Magento\Framework\DB\Select $gallerySelect): void
    {
        $videoTable = $this->resourceConnection->getTableName(self::GALLERY_VALUE_VIDEO_TABLE);

        if (!$this->resourceConnection->getConnection()->isTableExists($videoTable)) {
            return;
        }

        $gallerySelect->joinLeft(['store_video' => $videoTable], 'store_video.value_id = store_value.value_id AND store_video.store_id = store_value.store_id', []);
        $gallerySelect->where('store_video.value_id IS NULL');
    }

    protected function getStoreValueSelect(string $table, string $matchColumn): \Magento\Framework\DB\Select
    {
        $linkField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)->getLinkField();

        return $this->resourceConnection->getConnection()->select()
            ->from(['store_value' => $table], [])
            ->join(['default_value' => $table], sprintf('default_value.%1$s = store_value.%1$s AND default_value.%2$s = store_value.%2$s AND default_value.store_id = 0', $matchColumn, $linkField), [])
            ->join(['product' => $this->resourceConnection->getTableName('catalog_product_entity')], sprintf('product.%1$s = store_value.%1$s', $linkField), [])
            ->where('store_value.store_id <> ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
    }

    protected function addFilters(\Magento\Framework\DB\Select $select, array $productIds, ?int $storeId): \Magento\Framework\DB\Select
    {
        $select->where('product.entity_id IN (?)', $productIds);

        if ($storeId !== null) {
            $select->where('store_value.store_id = ?', $storeId);
        }

        return $select;
    }

    protected function countRows(\Magento\Framework\DB\Select $select): int
    {
        $select->columns(['count' => 'COUNT(*)']);

        return (int)$this->resourceConnection->getConnection()->fetchOne($select);
    }

    protected function deleteRows(\Magento\Framework\DB\Select $select): int
    {
        $connection = $this->resourceConnection->getConnection();

        return $connection->query($connection->deleteFromSelect($select, 'store_value'))->rowCount();
    }

    protected function getMediaImageAttributeIdsByTable(): array
    {
        if ($this->mediaImageAttributeIdsByTable !== null) {
            return $this->mediaImageAttributeIdsByTable;
        }

        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addFieldToFilter('frontend_input', 'media_image');
        $this->mediaImageAttributeIdsByTable = [];

        foreach ($attributeCollection as $attribute) {
            $this->mediaImageAttributeIdsByTable[$attribute->getBackendTable()][] = (int)$attribute->getAttributeId();
        }

        return $this->mediaImageAttributeIdsByTable;
    }
}
