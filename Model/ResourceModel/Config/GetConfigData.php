<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ResourceModel\Config;

use Magento\Framework\App\ResourceConnection;
use SoftCommerce\Profile\Api\Data\ConfigInterface;

/**
 * @inheritDoc
 */
class GetConfigData implements GetConfigDataInterface
{
    /**
     * @param ResourceConnection $resource
     */
    public function __construct(private ResourceConnection $resource)
    {}

    /**
     * @inheritDoc
     */
    public function execute(int $parentId): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                $this->resource->getTableName(ConfigInterface::DB_TABLE_NAME),
                [
                    ConfigInterface::SCOPE,
                    ConfigInterface::SCOPE_ID,
                    ConfigInterface::PATH,
                    ConfigInterface::VALUE
                ]
            )->where(ConfigInterface::PARENT_ID . ' = ?', $parentId);

        return $connection->fetchAll($select);
    }
}
