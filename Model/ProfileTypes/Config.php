<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ProfileTypes;

use Magento\Framework\Config\DataInterface;

/**
 * @inheritDoc
 */
class Config implements ConfigInterface
{
    /**
     * @var DataInterface
     */
    private $config;

    /**
     * @param DataInterface $config
     */
    public function __construct(DataInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getType(string $name): array
    {
        return $this->config->get('types/' . $name, []);
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->config->get('types') ?: [];
    }
}
