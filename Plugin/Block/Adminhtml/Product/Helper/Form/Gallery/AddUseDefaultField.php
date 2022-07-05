<?php

declare(strict_types=1);

namespace MageSuite\Media\Plugin\Block\Adminhtml\Product\Helper\Form\Gallery;

class AddUseDefaultField
{
    protected const FIELD_LABEL = 'Use default';
    public const FIELD_NAME = 'use_default_media';

    protected \Magento\Framework\Data\Form\Element\CheckboxFactory $checkboxField;
    protected \Magento\Framework\Data\Form $form;
    protected \Magento\Framework\App\RequestInterface $request;
    protected \MageSuite\Media\Model\ResourceModel\UseDefaultMediaAttribute $useDefaulMediaAttributeResourceModel;

    public function __construct(
        \Magento\Framework\Data\Form\Element\CheckboxFactory $checkboxFieldFactory,
        \Magento\Framework\Data\Form $form,
        \Magento\Framework\App\RequestInterface $request,
        \MageSuite\Media\Model\ResourceModel\UseDefaultMediaAttribute $useDefaulMediaAttributeResourceModel
    ) {
        $this->checkboxFieldFactory = $checkboxFieldFactory;
        $this->form = $form;
        $this->request = $request;
        $this->useDefaulMediaAttributeResourceModel = $useDefaulMediaAttributeResourceModel;
    }

    /**
     * @param \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(
        \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery $subject,
        string $result
    ): string {
        $storeId = (int) $this->request->getParam('store');
        $productId = (int) $this->request->getParam('id');

        if (empty($storeId)) {
            return $result;
        }

        $onchangeScript = sprintf(
            'var handle = document.getElementById("%s"); handle.value = handle.checked ? 1 : 0;',
            self::FIELD_NAME
        );

        /** @var \Magento\Framework\Data\Form\Element\Checkbox $field */
        $field = $this->checkboxFieldFactory->create(
            [
                'data' => [
                    'data-form-part' => 'product_form',
                    'name' => sprintf('product[%s]', self::FIELD_NAME),
                    'value' => 0,
                    'html_id' => self::FIELD_NAME,
                    'after_element_html' => self::FIELD_LABEL,
                    'onchange' => $onchangeScript,
                    'checked' => !$this->useDefaulMediaAttributeResourceModel->hasStoreData($productId, $storeId)
                ]
            ]
        );

        $field->setForm($this->form);

        $fieldHtml = sprintf('<div class="admin__field use_default_media">%s</div>', $field->getHtml());

        return $fieldHtml . $result;
    }
}
