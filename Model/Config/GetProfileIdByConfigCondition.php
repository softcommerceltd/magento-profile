<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\Config;

use SoftCommerce\Profile\Api\Data\ConfigInterface;
use SoftCommerce\Profile\Model\ResourceModel\Config;

/**
 * @inheritDoc
 */
class GetProfileIdByConfigCondition implements GetProfileIdByConfigConditionInterface
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * @param Config $resource
     */
    public function __construct(private readonly Config $resource)
    {}

    /**
     * @inheritDoc
     */
    public function execute(string $path, $value = null): array
    {
        if (isset($this->data[$path])) {
            return $this->data[$path] ?? [];
        }

        $data = $this->resource->getDataByPath($path, [ConfigInterface::PARENT_ID, ConfigInterface::VALUE]);
        if (null !== $value) {
            $data = array_filter($data, function ($item) use ($value) {
                return isset($item[ConfigInterface::VALUE]) && $item[ConfigInterface::VALUE] == $value;
            });
        }
        $this->data[$path] = array_column($data, ConfigInterface::PARENT_ID);

        return $this->data[$path];
    }
}
