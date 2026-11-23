<?php

declare(strict_types=1);

namespace MageSuite\Media\Test\Integration\Model\ResourceModel;

class RedundantStoreMediaDataTest extends \PHPUnit\Framework\TestCase
{
    protected const FIXTURE_SKU = 'simple';
    protected const SECOND_STORE_CODE = 'fixture_second_store';
    protected const DEFAULT_IMAGE = '/m/a/magento_image.jpg';
    protected const DEFAULT_THUMBNAIL = '/m/a/magento_thumbnail.jpg';

    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Framework\App\ResourceConnection $resourceConnection;
    protected ?\MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData $redundantStoreMediaData;
    protected int $productId;
    protected int $secondStoreId;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->resourceConnection = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $this->redundantStoreMediaData = $this->objectManager->create(\MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData::class);
    }

    #[\Magento\TestFramework\Fixture\AppArea('adminhtml')]
    #[\Magento\TestFramework\Fixture\AppIsolation(true)]
    #[\Magento\TestFramework\Fixture\DbIsolation(true)]
    #[\Magento\TestFramework\Fixture\DataFixtureBeforeTransaction('MageSuite_Media::Test/Integration/_files/product_with_multiple_images.php')]
    public function testItRemovesOnlyStoreDataIdenticalToDefault(): void
    {
        $this->prepareStoreData();
        $hiddenValueId = $this->getDefaultGalleryValueIds()[0];
        $this->updateStoreGalleryRow($hiddenValueId, ['disabled' => 1]);

        $result = $this->redundantStoreMediaData->removeRedundantData([$this->productId]);

        $this->assertSame(1, $result->getDataByKey(\MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData::GALLERY_ROWS));
        $this->assertSame(2, $result->getDataByKey(\MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData::ROLE_VALUES));
        $this->assertSame([$hiddenValueId], $this->getStoreGalleryValueIds());
        $this->assertSame(['thumbnail' => self::DEFAULT_IMAGE], $this->getStoreRoleValues());
    }

    #[\Magento\TestFramework\Fixture\AppArea('adminhtml')]
    #[\Magento\TestFramework\Fixture\AppIsolation(true)]
    #[\Magento\TestFramework\Fixture\DbIsolation(true)]
    #[\Magento\TestFramework\Fixture\DataFixtureBeforeTransaction('MageSuite_Media::Test/Integration/_files/product_with_multiple_images.php')]
    public function testItKeepsStoreDataThatDiffersFromDefault(): void
    {
        $this->prepareStoreData();
        [$firstValueId, $secondValueId] = $this->getDefaultGalleryValueIds();
        $this->updateStoreGalleryRow($firstValueId, ['position' => 99]);
        $this->updateStoreGalleryRow($secondValueId, ['label' => 'Store label']);

        $this->redundantStoreMediaData->removeRedundantData([$this->productId]);

        $this->assertSame([$firstValueId, $secondValueId], $this->getStoreGalleryValueIds());
    }

    #[\Magento\TestFramework\Fixture\AppArea('adminhtml')]
    #[\Magento\TestFramework\Fixture\AppIsolation(true)]
    #[\Magento\TestFramework\Fixture\DbIsolation(true)]
    #[\Magento\TestFramework\Fixture\DataFixtureBeforeTransaction('MageSuite_Media::Test/Integration/_files/product_with_multiple_images.php')]
    public function testItKeepsStoreDataDifferingOnlyInCaseOrAccents(): void
    {
        $this->prepareStoreData();
        [$firstValueId, $secondValueId] = $this->getDefaultGalleryValueIds();
        $this->updateStoreGalleryRow($firstValueId, ['label' => 'image alt text']);
        $this->updateStoreGalleryRow($secondValueId, ['label' => 'Thumbnail Imagé']);
        $this->setStoreRoleValues(['image' => strtoupper(self::DEFAULT_IMAGE)]);

        $this->redundantStoreMediaData->removeRedundantData([$this->productId]);

        $this->assertSame([$firstValueId, $secondValueId], $this->getStoreGalleryValueIds());
        $this->assertSame(strtoupper(self::DEFAULT_IMAGE), $this->getStoreRoleValues()['image'] ?? null);
    }

    #[\Magento\TestFramework\Fixture\AppIsolation(true)]
    #[\Magento\TestFramework\Fixture\DbIsolation(true)]
    #[\Magento\TestFramework\Fixture\DataFixtureBeforeTransaction('MageSuite_Media::Test/Integration/_files/product_with_multiple_images.php')]
    public function testItKeepsStoreGalleryRowWithStoreVideoData(): void
    {
        $this->prepareStoreData();
        [$videoValueId, $imageValueId] = $this->getDefaultGalleryValueIds();
        $this->resourceConnection->getConnection()->insert($this->resourceConnection->getTableName('catalog_product_entity_media_gallery_value_video'), [
            'value_id' => $videoValueId,
            'store_id' => $this->secondStoreId,
            'provider' => 'youtube',
            'url' => 'https://www.youtube.com/watch?v=store_view_video',
            'title' => 'Store view video title',
            'description' => 'Store view video description',
            'metadata' => '',
        ]);

        $this->redundantStoreMediaData->removeRedundantData([$this->productId]);

        $this->assertSame([$videoValueId], $this->getStoreGalleryValueIds());
        $this->assertNotContains($imageValueId, $this->getStoreGalleryValueIds());
    }

    #[\Magento\TestFramework\Fixture\AppIsolation(true)]
    #[\Magento\TestFramework\Fixture\DbIsolation(true)]
    #[\Magento\TestFramework\Fixture\DataFixtureBeforeTransaction('MageSuite_Media::Test/Integration/_files/product_with_multiple_images.php')]
    public function testItKeepsStoreGalleryRowWithoutDefaultRow(): void
    {
        $this->prepareStoreData();
        $storeOnlyValueId = $this->getDefaultGalleryValueIds()[0];
        $this->resourceConnection->getConnection()->delete($this->getGalleryValueTable(), ['value_id = ?' => $storeOnlyValueId, 'store_id = ?' => 0]);

        $this->redundantStoreMediaData->removeRedundantData([$this->productId]);

        $this->assertSame([$storeOnlyValueId], $this->getStoreGalleryValueIds());
    }

    #[\Magento\TestFramework\Fixture\AppArea('adminhtml')]
    #[\Magento\TestFramework\Fixture\AppIsolation(true)]
    #[\Magento\TestFramework\Fixture\DbIsolation(true)]
    #[\Magento\TestFramework\Fixture\DataFixtureBeforeTransaction('MageSuite_Media::Test/Integration/_files/product_with_multiple_images.php')]
    public function testDryRunCountsWithoutRemoving(): void
    {
        $this->prepareStoreData();
        $storeGalleryValueIds = $this->getStoreGalleryValueIds();

        $result = $this->redundantStoreMediaData->countRedundantData([$this->productId], $this->secondStoreId);

        $this->assertSame(2, $result->getDataByKey(\MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData::GALLERY_ROWS));
        $this->assertSame(2, $result->getDataByKey(\MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData::ROLE_VALUES));
        $this->assertSame($storeGalleryValueIds, $this->getStoreGalleryValueIds());
    }

    protected function prepareStoreData(): void
    {
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productId = (int)$productRepository->get(self::FIXTURE_SKU)->getId();
        $this->secondStoreId = (int)$this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore(self::SECOND_STORE_CODE)->getId();
        $connection = $this->resourceConnection->getConnection();

        $connection->delete($this->getGalleryValueTable(), ['entity_id = ?' => $this->productId, 'store_id = ?' => $this->secondStoreId]);

        foreach ($this->getDefaultGalleryValueIds() as $valueId) {
            $connection->insert($this->getGalleryValueTable(), [
                'value_id' => $valueId,
                'store_id' => $this->secondStoreId,
                'entity_id' => $this->productId,
                'label' => null,
                'position' => null,
                'disabled' => 0,
            ]);
        }

        $this->setStoreRoleValues(['image' => self::DEFAULT_IMAGE, 'small_image' => self::DEFAULT_IMAGE, 'thumbnail' => self::DEFAULT_IMAGE]);
    }

    protected function setStoreRoleValues(array $values): void
    {
        $connection = $this->resourceConnection->getConnection();
        $eavConfig = $this->objectManager->get(\Magento\Eav\Model\Config::class);

        foreach ($values as $attributeCode => $value) {
            $attribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            $connection->delete($attribute->getBackendTable(), ['attribute_id = ?' => $attribute->getId(), 'entity_id = ?' => $this->productId, 'store_id = ?' => $this->secondStoreId]);
            $connection->insert($attribute->getBackendTable(), [
                'attribute_id' => $attribute->getId(),
                'store_id' => $this->secondStoreId,
                'entity_id' => $this->productId,
                'value' => $value,
            ]);
        }

        $connection->delete($eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'swatch_image')->getBackendTable(), [
            'attribute_id = ?' => $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'swatch_image')->getId(),
            'entity_id = ?' => $this->productId,
            'store_id = ?' => $this->secondStoreId,
        ]);
    }

    protected function getStoreRoleValues(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $eavConfig = $this->objectManager->get(\Magento\Eav\Model\Config::class);
        $values = [];

        foreach (['image', 'small_image', 'thumbnail'] as $attributeCode) {
            $attribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            $select = $connection->select()
                ->from($attribute->getBackendTable(), 'value')
                ->where('attribute_id = ?', $attribute->getId())
                ->where('entity_id = ?', $this->productId)
                ->where('store_id = ?', $this->secondStoreId);
            $value = $connection->fetchOne($select);

            if ($value !== false) {
                $values[$attributeCode] = $value;
            }
        }

        return $values;
    }

    protected function getDefaultGalleryValueIds(): array
    {
        return $this->getGalleryValueIds(0);
    }

    protected function getStoreGalleryValueIds(): array
    {
        return $this->getGalleryValueIds($this->secondStoreId);
    }

    protected function getGalleryValueIds(int $storeId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->getGalleryValueTable(), 'value_id')
            ->where('entity_id = ?', $this->productId)
            ->where('store_id = ?', $storeId)
            ->order('value_id ASC');

        return array_map('intval', $connection->fetchCol($select));
    }

    protected function updateStoreGalleryRow(int $valueId, array $data): void
    {
        $this->resourceConnection->getConnection()->update($this->getGalleryValueTable(), $data, ['value_id = ?' => $valueId, 'store_id = ?' => $this->secondStoreId]);
    }

    protected function getGalleryValueTable(): string
    {
        return $this->resourceConnection->getTableName(\Magento\Catalog\Model\ResourceModel\Product\Gallery::GALLERY_VALUE_TABLE);
    }
}
