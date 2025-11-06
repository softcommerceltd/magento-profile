<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\Config;

use SoftCommerce\Profile\Model\ResourceModel\Config;

/**
 * @inheritDoc
 */
class GetProfileConfigValueByPath implements GetProfileConfigValueByPathInterface
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * @param Config $resource
     */
    public function __construct(
        private readonly Config $resource
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $path, bool $isLooseComparison = false): array
    {
        if (!isset($this->data[$path])) {
            $this->data[$path] = $isLooseComparison
                ? $this->resource->getSearchConfigByPathAlike($path)
                : $this->resource->getDataByPath($path);
        }

        return $this->data[$path];
    }
}
