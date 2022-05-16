<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

/**
 * Interface GetProfileTypeIdInterface used to
 * provide profile type ID.
 */
interface GetProfileTypeIdInterface
{
    /**
     * @param int $profileId
     * @return string
     */
    public function execute(int $profileId): string;

    /**
     * @param int|null $profileId
     * @return void
     */
    public function resetData(?int $profileId = null): void;
}
