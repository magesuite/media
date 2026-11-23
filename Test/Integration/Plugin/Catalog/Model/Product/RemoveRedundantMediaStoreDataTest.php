<?php

declare(strict_types=1);

namespace MageSuite\Media\Test\Integration\Plugin\Catalog\Model\Product;

class RemoveRedundantMediaStoreDataTest extends \PHPUnit\Framework\TestCase
{
    protected const FIXTURE_SKU = 'simple';
    protected const SECOND_STORE_CODE = 'fixture_second_store';

    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\Magento\Framework\App\ResourceConnection $resourceConnection;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->resourceConnection = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class);
    }

    #[\Magento\TestFramework\Fixture\AppArea('adminhtml')]
    #[\Magento\TestFramework\Fixture\AppIsolation(true)]
    #[\Magento\TestFramework\Fixture\DbIsolation(true)]
    #[\Magento\TestFramework\Fixture\Config(\MageSuite\Media\Helper\Configuration::XML_PATH_REMOVE_REDUNDANT_STORE_DATA_ON_SAVE, 1)]
    #[\Magento\TestFramework\Fixture\DataFixtureBeforeTransaction('MageSuite_Media::Test/Integration/_files/product_with_multiple_images.php')]
    public function testSavingProductInStoreViewDoesNotLeaveStoreDataIdenticalToDefault(): void
    {
        $storeId = $this->saveProductInSecondStore();

        $this->assertSame([], $this->getStoreGalleryValueIds($storeId));
        $this->assertSame('/m/a/magento_image.jpg', $this->productRepository->get(self::FIXTURE_SKU, false, $storeId, true)->getThumbnail());
    }

    #[\Magento\TestFramework\Fixture\AppArea('adminhtml')]
    #[\Magento\TestFramework\Fixture\AppIsolation(true)]
    #[\Magento\TestFramework\Fixture\DbIsolation(true)]
    #[\Magento\TestFramework\Fixture\Config(\MageSuite\Media\Helper\Configuration::XML_PATH_REMOVE_REDUNDANT_STORE_DATA_ON_SAVE, 0)]
    #[\Magento\TestFramework\Fixture\DataFixtureBeforeTransaction('MageSuite_Media::Test/Integration/_files/product_with_multiple_images.php')]
    public function testSavingProductInStoreViewKeepsStoreDataWhenDisabled(): void
    {
        $storeId = $this->saveProductInSecondStore();

        $this->assertNotEmpty($this->getStoreGalleryValueIds($storeId));
    }

    protected function saveProductInSecondStore(): int
    {
        $storeId = (int)$this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore(self::SECOND_STORE_CODE)->getId();
        $product = $this->productRepository->get(self::FIXTURE_SKU, true, $storeId, true);
        $product->setName('Store view name');
        $this->productRepository->save($product);

        return $storeId;
    }

    protected function getStoreGalleryValueIds(int $storeId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $linkField = $this->objectManager->get(\Magento\Framework\EntityManager\MetadataPool::class)->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)->getLinkField();
        $productLinkId = (int)$this->productRepository->get(self::FIXTURE_SKU)->getData($linkField);
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName(\Magento\Catalog\Model\ResourceModel\Product\Gallery::GALLERY_VALUE_TABLE), 'value_id')
            ->where(sprintf('%s = ?', $linkField), $productLinkId)
            ->where('store_id = ?', $storeId);

        return $connection->fetchCol($select);
    }
}
