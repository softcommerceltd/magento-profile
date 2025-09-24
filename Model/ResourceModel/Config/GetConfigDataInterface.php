<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ResourceModel\Config;

/**
 * Interface GetConfigDataInterface
 */
interface GetConfigDataInterface
{
    /**
     * @param int $parentId
     * @return array
     */
    public function execute(int $parentId): array;
}