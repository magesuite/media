<?php

declare(strict_types=1);

namespace MageSuite\Media\Console\Command;

class RemoveRedundantStoreMediaData extends \Symfony\Component\Console\Command\Command
{
    protected const COMMAND_NAME = 'magesuite:media:remove-redundant-store-data';
    protected const COMMAND_DESCRIPTION = 'Remove store view image data (gallery values and image roles) identical to the default values';
    protected const OPTION_DRY_RUN = 'dry-run';
    protected const OPTION_STORE = 'store';
    protected const OPTION_PRODUCT_IDS = 'product-ids';

    public function __construct(
        protected \MageSuite\Media\Service\RemoveRedundantStoreMediaDataFactory $removeRedundantStoreMediaDataFactory,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription(self::COMMAND_DESCRIPTION);
        $this->addOption(self::OPTION_DRY_RUN, null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Only count the data that would be removed');
        $this->addOption(self::OPTION_STORE, null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Store view ID, all store views when omitted');
        $this->addOption(self::OPTION_PRODUCT_IDS, null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Comma separated product IDs, all products when omitted');
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
    {
        $dryRun = (bool)$input->getOption(self::OPTION_DRY_RUN);
        $result = $this->removeRedundantStoreMediaDataFactory->create()->execute($this->getProductIds($input), $this->getStoreId($input), $dryRun);
        $verb = $dryRun ? 'To remove' : 'Removed';

        $output->writeln(sprintf(
            '%s: %d gallery rows, %d image role values',
            $verb,
            $result->getDataByKey(\MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData::GALLERY_ROWS),
            $result->getDataByKey(\MageSuite\Media\Model\ResourceModel\RedundantStoreMediaData::ROLE_VALUES)
        ));

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    protected function getStoreId(\Symfony\Component\Console\Input\InputInterface $input): ?int
    {
        $storeId = $input->getOption(self::OPTION_STORE);

        return $storeId === null ? null : (int)$storeId;
    }

    protected function getProductIds(\Symfony\Component\Console\Input\InputInterface $input): ?array
    {
        $productIds = $input->getOption(self::OPTION_PRODUCT_IDS);

        if ($productIds === null) {
            return null;
        }

        return array_values(array_filter(array_map('intval', explode(',', $productIds))));
    }
}
