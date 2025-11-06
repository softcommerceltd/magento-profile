<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use SoftCommerce\Profile\Api\Data\ConfigInterface;
use SoftCommerce\Profile\Model\ResourceModel\Config;

/**
 * @inheritDoc
 */
class ConfigScopeWriter implements ConfigScopeWriterInterface
{
    /**
     * @param ConfigScopeInterface $configScope
     * @param Config $resource
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly ConfigScopeInterface $configScope,
        private readonly Config $resource,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * @inheritDoc
     */
    public function save(
        int $profileId,
        string $path,
        mixed $value,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): void
    {
        if (is_array($value)) {
            try {
                $value = $this->serializer->serialize($value);
            } catch (\InvalidArgumentException $e) {
                $value = null;
            }
        }

        $saveRequest = [
            ConfigInterface::PARENT_ID => $profileId,
            ConfigInterface::SCOPE => $scope,
            ConfigInterface::SCOPE_ID => $scopeId,
            ConfigInterface::PATH => $path,
            ConfigInterface::VALUE => $value
        ];

        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getMainTable())
            ->where(ConfigInterface::PARENT_ID . ' = ?', $profileId)
            ->where(ConfigInterface::PATH . ' = ?', $path)
            ->where(ConfigInterface::SCOPE . ' = ?', $scope)
            ->where(ConfigInterface::SCOPE_ID . ' = ?', $scopeId);

        if ($existingItem = $connection->fetchRow($select)) {
            $this->resource->update(
                $saveRequest,
                [ConfigInterface::ENTITY_ID . ' = ?' => $existingItem[ConfigInterface::ENTITY_ID]]
            );
        } else {
            $this->resource->insert($saveRequest);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(
        int $profileId,
        string $path,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): void
    {
        $this->resource->remove(
            [
                ConfigInterface::PARENT_ID . ' = ?' => $profileId,
                ConfigInterface::PATH . ' = ?' => $path,
                ConfigInterface::SCOPE . ' = ?' => $scope,
                ConfigInterface::SCOPE_ID . ' = ?' => $scopeId
            ]
        );
    }

    /**
     * @return void
     */
    public function clean(): void
    {
        $this->configScope->clean();
    }
}
