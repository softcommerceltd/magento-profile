<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ProfileTypes;

/**
 * Interface ConfigInterface
 */
interface ConfigInterface
{
    /**
     * @param string $name
     * @return array
     */
    public function getType(string $name): array;

    /**
     * @return array
     */
    public function getAll(): array;
}
