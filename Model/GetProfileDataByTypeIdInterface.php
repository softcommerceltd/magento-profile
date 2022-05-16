<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

/**
 * Interface GetProfileDataByTypeIdInterface used to
 * provide profile cached data in array format.
 */
interface GetProfileDataByTypeIdInterface
{
    /**
     * @param string $typeId
     * @param string|null $metadata
     * @return array|mixed|null
     */
    public function execute(string $typeId, ?string $metadata = null);

    /**
     * @param string|null $typeId
     * @return void
     */
    public function resetData(?string $typeId = null): void;
}
