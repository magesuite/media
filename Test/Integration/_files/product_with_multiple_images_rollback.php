<?php

declare(strict_types=1);

\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()
    ->requireDataFixture('Magento/CatalogUrlRewrite/_files/product_with_stores_rollback.php');
\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()
    ->requireDataFixture('Magento/Catalog/_files/product_image_rollback.php');
