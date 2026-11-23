<?php

declare(strict_types=1);

namespace MageSuite\Media\Helper;

class Configuration
{
    public const XML_PATH_REMOVE_REDUNDANT_STORE_DATA_ON_SAVE = 'magesuite_media/redundant_store_data/remove_on_save';
    public const XML_PATH_REMOVE_REDUNDANT_STORE_DATA_BY_CRON = 'magesuite_media/redundant_store_data/remove_by_cron';

    public function __construct(
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isRemovingRedundantStoreDataOnSaveEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REMOVE_REDUNDANT_STORE_DATA_ON_SAVE);
    }

    public function isRemovingRedundantStoreDataByCronEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REMOVE_REDUNDANT_STORE_DATA_BY_CRON);
    }
}
