<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Profile\Model\GetProfileTypeIdInterface;

/**
 * Class AbstractConfig provides global configuration data
 * for profile entities and serves as a main wrapper class.
 */
class ConfigModel extends DataObject
{
    public const MAGENTO_ATTRIBUTE = 'magento_attribute';
    public const CLIENT_ATTRIBUTE = 'client_attribute';
    public const PLENTY_ATTRIBUTE = 'plenty_attribute';

    /**
     * @var int|null
     */
    protected ?int $profileId;

    /**
     * @var string[]
     */
    private array $typeIdInMemory = [];

    /**
     * @param ConfigScopeInterface $configScope
     * @param CollectionFactory $dataCollectionFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param GetProfileTypeIdInterface $getProfileTypeId
     * @param SerializerInterface $serializer
     * @param array $data
     * @param int|null $profileId
     */
    public function __construct(
        protected ConfigScopeInterface $configScope,
        protected CollectionFactory $dataCollectionFactory,
        protected DataObjectFactory $dataObjectFactory,
        protected DataStorageInterfaceFactory $dataStorageFactory,
        protected GetProfileTypeIdInterface $getProfileTypeId,
        protected SerializerInterface $serializer,
        array $data = [],
        ?int $profileId = null
    ) {
        $this->profileId = $profileId;
        parent::__construct($data);
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    protected function getProfileId(): int
    {
        if (!$this->profileId) {
            throw new LocalizedException(__('Profile ID is not set'));
        }
        return $this->profileId;
    }

    /**
     * @param int $profileId
     * @return $this
     */
    public function setProfileId(int $profileId): static
    {
        $this->profileId = $profileId;
        return $this;
    }

    /**
     * @param string $xmlPath
     * @param int|string|null $store [store/website id/code]
     * @param string $scope
     * @return array|mixed|null
     * @throws LocalizedException
     */
    public function getConfig(
        string $xmlPath,
        $store = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ): mixed
    {
        $indexKey = $this->getIndexKey($xmlPath, $store, $scope);
        if (!$this->hasData($indexKey)) {
            $this->setData($indexKey, $this->configScope->get($this->getProfileId(), $xmlPath, $scope, $store));
        }

        return $this->getData($indexKey);
    }

    /**
     * @param string $xmlPath
     * @param null $store
     * @param string $scope
     * @return array
     * @throws LocalizedException
     */
    public function getConfigDataSerialized(
        string $xmlPath,
        $store = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ): array
    {
        if (!$data = $this->getConfig($xmlPath, $store, $scope)) {
            return [];
        }

        try {
            $data = $this->serializer->unserialize($data);
        } catch (\InvalidArgumentException $e) {
            $data = [];
        }

        return is_array($data) ? $data : [$data];
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getTypeId(): string
    {
        if (!isset($this->typeIdInMemory[$this->getProfileId()])) {
            $this->typeIdInMemory[$this->getProfileId()] = $this->getProfileTypeId->execute($this->getProfileId());
        }
        return $this->typeIdInMemory[$this->getProfileId()];
    }

    /**
     * @param array $dataMap
     * @return array
     */
    protected function generateAttributeDataMap(array $dataMap): array
    {
        $result = [];
        foreach ($dataMap as $item) {
            if (isset($item[self::MAGENTO_ATTRIBUTE])) {
                $result[$item[self::MAGENTO_ATTRIBUTE]] = $item[self::CLIENT_ATTRIBUTE]
                    ?? ($item[self::PLENTY_ATTRIBUTE] ?? null);
            }
        }

        return $result;
    }

    /**
     * @param string $xmlPath
     * @param int|string|null $store
     * @param string $scope
     * @return string
     * @throws LocalizedException
     */
    private function getIndexKey(
        string $xmlPath,
        int|string|null $store = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ): string
    {
        $store = $store ?: 0;
        $result = "{$this->getProfileId()}_{$store}_{$scope}_";
        $result .= str_replace('/', '_', trim($xmlPath));
        return $result;
    }
}
