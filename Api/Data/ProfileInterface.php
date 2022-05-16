<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Api\Data;

/**
 * Interface ProfileInterface
 * used to provide profile entity data.
 */
interface ProfileInterface
{
    public const DB_TABLE_NAME = 'softcommerce_profile_entity';

    public const ID = 'id';
    public const ENTITY_ID = 'entity_id';
    public const NAME = 'name';
    public const STATUS = 'status';
    public const TYPE_ID = 'type_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const PROFILE_ID = 'profile_id';

    /**
     * @return int
     */
    public function getEntityId(): int;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name);

    /**
     * @return string|null
     */
    public function getTypeId(): ?string;

    /**
     * @param string $name
     * @return $this
     */
    public function setTypeId(string $name);

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt);

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt);
}
