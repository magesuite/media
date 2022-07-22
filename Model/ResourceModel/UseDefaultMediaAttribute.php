<?php

declare(strict_types=1);

namespace MageSuite\Media\Model\ResourceModel;

class UseDefaultMediaAttribute
{
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \Magento\Eav\Model\Config $eavConfig;
    protected \Magento\Framework\EntityManager\MetadataPool $metadataPool;

    protected array $mediaImageAttributes = [];

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->connection = $resourceConnection->getConnection();
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @return bool
     */
    public function hasStoreData(int $productId, int $storeId): bool
    {
        $galleryTables = ['catalog_product_entity_gallery', 'catalog_product_entity_media_gallery_value'];

        foreach ($galleryTables as $galleryTable) {
            $galleryMediaTable = $this->connection->getTableName($galleryTable);
            $findRow = $this->existValuesInTable($galleryMediaTable, $storeId, $productId);

            if (!empty($findRow)) {
                return true;
            }
        }

        $attributes = $this->getMediaImageAttributes();

        foreach ($attributes as $attribute) {
            $entityTable = $this->connection->getTableName(
                sprintf('catalog_product_entity_%s', $attribute['backend_type'])
            );

            $findRow = $this->existValuesInTable($entityTable, $storeId, $productId, (int) $attribute['attribute_id']);

            if ($findRow === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @return int
     */
    public function removeStoreData(int $productId, int $storeId): int
    {
        if (empty($storeId) || empty($productId)) {
            return 0;
        }

        $linkField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)->getLinkField();

        $where[] = $this->connection->quoteInto('store_id = ?', $storeId);

        $ids = $this->getProductRowsId($linkField, $productId);
        $where[] = $this->connection->quoteInto("{$linkField} in (?)", $ids);

        $entityTable = $this->connection->getTableName('catalog_product_entity_gallery');

        $deleted = $this->connection->delete($entityTable, $where);

        $entityTable = $this->connection->getTableName('catalog_product_entity_media_gallery_value');
        $deleted += $this->connection->delete($entityTable, $where);

        $deleted += $this->removeMediaImageStoreData($where, $deleted);

        return $deleted;
    }

    /**
     * @param string $where
     * @param int $deleted
     * @return int
     */
    protected function removeMediaImageStoreData(array $where, int $deleted): int
    {
        $attributes = $this->getMediaImageAttributes();

        foreach ($attributes as $attribute) {
            $attributeWhere = $this->connection->quoteInto('attribute_id = ?', $attribute['attribute_id']);
            array_push($where, $attributeWhere);

            $entityTable = $this->connection->getTableName(
                sprintf('catalog_product_entity_%s', $attribute['backend_type'])
            );
            $deleted += $this->connection->delete($entityTable, $where);

            array_pop($where);
        }

        return $deleted;
    }

    /**
     * @return array
     */
    protected function getMediaImageAttributes(): array
    {
        if (empty($this->mediaImageAttributes)) {
            $entityTypeId = $this->eavConfig
                ->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
                ->getEntityTypeId();

            $select = $this->connection->select()->from('eav_attribute', ['attribute_id', 'backend_type'])
                ->where('frontend_input = "media_image"')
                ->where('backend_type != "static"')
                ->where('backend_type is not null')
                ->where('backend_type <> ""')
                ->where('entity_type_id = ?', $entityTypeId);

            $this->mediaImageAttributes = $this->connection->fetchAll($select);
        }

        return $this->mediaImageAttributes;
    }

    /**
     * @param $backendType
     * @param int $storeId
     * @param int $productId
     * @return array|bool
     */
    protected function existValuesInTable($tableName, int $storeId, int $productId, int $attributeId = null) //phpcs:ignore
    {
        $linkField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)->getLinkField();
        $catalogProductEntityTableName = $this->connection->getTableName('catalog_product_entity');

        $select = $this->connection->select()->from($tableName, 'value_id')
            ->join(
                ['product' => $catalogProductEntityTableName],
                "product.{$linkField} = main_table.{$linkField}",
                ''
            )
            ->where('store_id = ?', $storeId);

        $select->limit(1);

        if (!empty($attributeId)) {
            $select->where('attribute_id = ?', $attributeId);
        }

        return (bool)$this->connection->fetchCol($select);
    }

    /**
     * @param string $linkField
     * @param int $productId
     * @return array
     */
    protected function getProductRowsId(string $linkField, int $productId): array
    {
        $tableName = $this->connection->getTableName('catalog_product_entity');
        $select = $this->connection->select()->from($tableName, $linkField)
            ->where('entity_id = ?', $productId);

        return $this->connection->fetchCol($select);
    }
}
