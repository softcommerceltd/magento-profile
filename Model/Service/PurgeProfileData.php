<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\Service;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use SoftCommerce\Core\Model\Trait\ConnectionTrait;
use SoftCommerce\Profile\Api\Service\PurgeProfileDataInterface;

/**
 * Service for purging profile-related data from database tables
 */
class PurgeProfileData implements PurgeProfileDataInterface
{
    use ConnectionTrait;

    /**
     * List of tables to purge (without table prefix)
     */
    private const TABLES_TO_PURGE = [
        'softcommerce_profile_config',
        'softcommerce_profile_history',
        'softcommerce_profile_notification',
        'softcommerce_profile_notification_summary',
        'softcommerce_profile_queue',
        'softcommerce_profile_schedule'
    ];

    /**
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(?int $profileId = null): void
    {
        $connection = $this->getConnection();

        try {
            $connection->beginTransaction();

            foreach ($this->getTablesToPurge() as $tableName) {
                $table = $this->resourceConnection->getTableName($tableName);

                if (!$connection->isTableExists($table)) {
                    $this->logger->warning(sprintf('Table %s does not exist, skipping.', $table));
                    continue;
                }

                if ($profileId !== null) {
                    // Check if profile_id column exists
                    $columns = $connection->describeTable($table);
                    if (isset($columns['profile_id'])) {
                        $connection->delete($table, ['profile_id = ?' => $profileId]);
                        $this->logger->info(sprintf(
                            'Purged data from table %s for profile ID %d',
                            $table,
                            $profileId
                        ));
                    } else {
                        $this->logger->warning(sprintf(
                            'Table %s does not have profile_id column, skipping profile-specific purge.',
                            $table
                        ));
                    }
                } else {
                    $connection->truncateTable($table);
                    $this->logger->info(sprintf('Truncated table %s', $table));
                }
            }

            $connection->commit();
            $this->logger->info('Profile data purge completed successfully.');
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->error('Failed to purge profile data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function getTablesToPurge(): array
    {
        return self::TABLES_TO_PURGE;
    }

    /**
     * @inheritdoc
     */
    public function canPurge(): bool
    {
        // Add any business logic here to determine if purge is allowed
        // For example, check if user has permission, if system is in maintenance mode, etc.
        return true;
    }
}
