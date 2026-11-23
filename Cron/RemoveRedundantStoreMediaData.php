<?php

declare(strict_types=1);

namespace MageSuite\Media\Cron;

class RemoveRedundantStoreMediaData
{
    public function __construct(
        protected \MageSuite\Media\Service\RemoveRedundantStoreMediaData $removeRedundantStoreMediaData,
        protected \MageSuite\Media\Helper\Configuration $configuration
    ) {
    }

    public function execute(): void
    {
        if (!$this->configuration->isRemovingRedundantStoreDataByCronEnabled()) {
            return;
        }

        $this->removeRedundantStoreMediaData->execute();
    }
}
