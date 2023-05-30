<?php

declare(strict_types=1);

namespace MageSuite\Media\Plugin\Catalog\Model\Product;

class RemoveMediaStoreData
{
    protected \Magento\Framework\App\RequestInterface $request;
    protected \MageSuite\Media\Model\ResourceModel\UseDefaultMediaAttribute $useDefaultMediaAttributeResourceModel;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \MageSuite\Media\Model\ResourceModel\UseDefaultMediaAttribute $useDefaultMediaAttributeResourceModel
    ) {
        $this->request = $request;
        $this->useDefaultMediaAttributeResourceModel = $useDefaultMediaAttributeResourceModel;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $brand
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function afterAfterSave(
        \Magento\Catalog\Api\Data\ProductInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        if ($this->restoreToDefault((int)$product->getStoreId()) === false) {
            return $product;
        }

        $this->useDefaultMediaAttributeResourceModel->removeStoreData(
            (int) $product->getId(),
            (int) $product->getStoreId()
        );

        return $product;
    }

    /**
     * @return bool
     */
    public function restoreToDefault(int $storeId): bool
    {
        $data = $this->request->getPostValue();
        $productData = $data['product'] ?? [];

        return !empty($productData[\MageSuite\Media\Plugin\Block\Adminhtml\Product\Helper\Form\Gallery\AddUseDefaultField::FIELD_NAME])
            && !empty($storeId);
    }
}
