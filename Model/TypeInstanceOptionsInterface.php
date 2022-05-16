<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

use SoftCommerce\Profile\Api\Data\ProfileInterface;

/**
 * Interface TypeInstanceOptionsInterface
 */
interface TypeInstanceOptionsInterface
{
    /**
     * @return array
     */
    public function getOptionArray(): array;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param string|null $typeId
     * @return array
     */
    public function getTypes(?string $typeId = null): array;

    /**
     * @param ProfileInterface $profile
     * @return string|null
     */
    public function getRouter(ProfileInterface $profile): ?string;

    /**
     * @param string $typeId
     * @return string|null
     */
    public function getRouterByTypeId(string $typeId): ?string;

    /**
     * @param string $typeId
     * @return string|null
     */
    public function getQueueRouterByTypeId(string $typeId): ?string;

    /**
     * @param string $typeId
     * @return string|null
     */
    public function getCronGroupByTypeId(string $typeId): ?string;
}
