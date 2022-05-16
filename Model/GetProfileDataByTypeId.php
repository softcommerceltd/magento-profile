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
class GetProfileDataByTypeId implements GetProfileDataByTypeIdInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var ResourceModel\Profile
     */
    private $resource;

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
    public function execute(string $typeId, ?string $metadata = null)
    {
        if (!isset($this->data[$typeId])) {
            $this->data[$typeId] = $this->getData($typeId);
        }

        return null !== $metadata
            ? ($this->data[$typeId][$metadata] ?? null)
            : $this->data[$typeId] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function resetData(?string $typeId = null): void
    {
        if (null !== $typeId) {
            $this->data[$typeId] = null;
        } else {
            $this->data = null;
        }
    }

    /**
     * @param string $typeId
     * @return array
     */
    private function getData(string $typeId): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName(ProfileInterface::DB_TABLE_NAME))
            ->where(ProfileInterface::TYPE_ID . ' = ?', $typeId);
        return $connection->fetchRow($select) ?: [];
    }
}
