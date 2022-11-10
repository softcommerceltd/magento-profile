<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Model\ResourceModel;

/**
 * @inheritDoc
 */
class GetProfileTypeId implements GetProfileTypeIdInterface
{
    /**
     * @var array
     */
    private array $data;

    /**
     * @var ResourceModel\Profile
     */
    private ResourceModel\Profile $resource;

    /**
     * @param ResourceModel\Profile $resource
     */
    public function __construct(ResourceModel\Profile $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @inheritDoc
     */
    public function execute(int $profileId): string
    {
        if (!isset($this->data[$profileId])) {
            $this->data[$profileId] = $this->getData($profileId);
        }

        return $this->data[$profileId];
    }

    /**
     * @inheritDoc
     */
    public function resetData(?int $profileId = null): void
    {
        if (null !== $profileId) {
            $this->data[$profileId] = null;
        } else {
            $this->data = [];
        }
    }

    /**
     * @param int $profileId
     * @return string
     */
    private function getData(int $profileId): string
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName(ProfileInterface::DB_TABLE_NAME), ProfileInterface::TYPE_ID)
            ->where(ProfileInterface::ENTITY_ID . ' = ?', $profileId);
        return (string) $connection->fetchOne($select);
    }
}
