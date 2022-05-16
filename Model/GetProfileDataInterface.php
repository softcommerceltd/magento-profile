<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

/**
 * Interface GetProfileDataInterface used to
 * provide profile data in array format.
 */
interface GetProfileDataInterface
{
    /**
     * @return array
     */
    public function execute(): array;

    /**
     * @param string $searchKey
     * @param string|array|mixed $searchValue
     * @return array
     */
    public function applySearchCriteria(string $searchKey, $searchValue): array;
}
