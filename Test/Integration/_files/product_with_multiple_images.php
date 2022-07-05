<?php

declare(strict_types=1);

\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()
    ->requireDataFixture('Magento/Catalog/_files/product_image.php');
\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()
    ->requireDataFixture('Magento/CatalogUrlRewrite/_files/product_with_stores.php');

/** @var \Magento\Framework\App\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

/** @var \Magento\Catalog\Api\Data\ProductInterface $product */
$product = $productRepository->get('simple');
$product->setStoreId(0)
    ->setImage('/m/a/magento_image.jpg')
    ->setSmallImage('/m/a/magento_image.jpg')
    ->setThumbnail('/m/a/magento_thumbnail.jpg')
    ->setSwatchImage('/m/a/magento_thumbnail.jpg')
    ->setData('media_gallery', ['images' => [
        [
            'file' => '/m/a/magento_image.jpg',
            'position' => 1,
            'label' => 'Image Alt Text',
            'disabled' => 0,
            'media_type' => 'image'
        ],
        [
            'file' => '/m/a/magento_thumbnail.jpg',
            'position' => 2,
            'label' => 'Thumbnail Image',
            'disabled' => 0,
            'media_type' => 'image'
        ],
    ]])
    ->setCanSaveCustomOptions(true)
    ->save();

$store = $storeManager->getStore('fixture_second_store');

$gallery = $product->getMediaGalleryImages();

$product
    ->setStoreId($store->getId())
    ->setThumbnail($gallery->getFirstItem()->getFile())
    ->setCanSaveCustomOptions(true)
    ->save();
