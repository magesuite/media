<?php

declare(strict_types=1);

namespace MageSuite\Media\Test\Integration\Plugin\Catalog\Model\Product\RemoveMediaStoreDataTest;

class RemoveMediaStoreDataTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\Framework\App\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\Magento\Framework\App\RequestInterface $request;
    protected ?\Magento\Store\Model\StoreManagerInterface $storeManager;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->request = $this->objectManager->get(\Magento\Framework\App\RequestInterface::class);
        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixtureBeforeTransaction MageSuite_Media::Test/Integration/_files/product_with_multiple_images.php
     */
    public function testRemoveStoreData(): void
    {
        $product = $this->productRepository->get('simple', false, 0, true);
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $product->getThumbnail());

        $secondStore = $this->storeManager->getStore('fixture_second_store');

        $product = $this->productRepository->get('simple', false, $secondStore->getId(), true);
        $this->assertEquals('/m/a/magento_image.jpg', $product->getThumbnail());

        $this->request->setPostValue('product', ['use_default_media' => 1]);
        $this->productRepository->save($product);

        $product = $this->productRepository->get('simple', false, $secondStore->getId(), true);
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $product->getThumbnail());
    }
}
