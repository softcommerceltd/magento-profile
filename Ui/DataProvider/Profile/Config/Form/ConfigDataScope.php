<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\DataProvider\Profile\Config\Form;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use SoftCommerce\ProfileConfig\Model\ConfigScopeInterface;

/**
 * @inheritDoc
 */
class ConfigDataScope implements ConfigDataScopeInterface
{
    /**
     * @var ConfigScopeInterface
     */
    private $configScope;

    /**
     * @var string|null
     */
    private $entity;

    /**
     * @var bool|null
     */
    private $isDefaultValueInMemory;

    /**
     * @var int|null
     */
    private $profileId;

    /**
     * @var string|null
     */
    private $scope;

    /**
     * @var int|string|null
     */
    private $scopeId;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ConfigScopeInterface $configScope
     * @param SerializerInterface $serializer
     * @param array $data
     * @throws \Exception
     */
    public function __construct(
        ConfigScopeInterface $configScope,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->configScope = $configScope;
        $this->serializer = $serializer;
        $this->init($data);
    }

    /**
     * @inheritDoc
     */
    public function get(?string $path = null, bool $unserialized = false)
    {
        return $this->getData($path, $this->scope, $this->scopeId, $unserialized);
    }

    /**
     * @return string
     */
    public function getCurrentScope(): string
    {
        return $this->scope ?: ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
    }

    /**
     * @return int
     */
    public function getCurrentScopeId(): int
    {
        return (int) $this->scopeId;
    }

    /**
     * @return bool
     */
    public function isCurrentScopeDefault(): bool
    {
        return $this->getCurrentScope() === ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
    }

    /**
     * @inheritDoc
     */
    public function isDefaultValue(string $path): bool
    {
        if ($this->isCurrentScopeDefault()) {
            return true;
        }

        if (!isset($this->isDefaultValueInMemory[$path])) {
            $defaultData = $this->getData($path, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, true);
            $scopeData = $this->getData($path, $this->scope, $this->scopeId, true);
            $this->isDefaultValueInMemory[$path] = $defaultData === $scopeData;
        }

        return $this->isDefaultValueInMemory[$path] ?? true;
    }

    /**
     * @param string|null $path
     * @param string $scope
     * @param string|int|null $scopeId
     * @param bool $unserialized
     * @return array|int|string|mixed|null
     * @throws \Exception
     */
    private function getData(
        ?string $path = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = null,
        bool $unserialized = false
    ) {
        if (null === $this->profileId
            || null === $this->entity
            || !$data = $this->configScope->get($this->profileId, $path, $scope, $scopeId)
        ) {
            return [];
        }

        if (null === $path) {
            return $data[$this->entity] ?? [];
        }

        if (false !== $unserialized) {
            try {
                $data = $this->serializer->unserialize($data);
            } catch (\InvalidArgumentException $e) {
                $data = [];
            }
        }

        return $data;
    }

    /**
     * @param array $request
     * @return ConfigDataScope
     * @throws \Exception
     */
    private function init(array $request): ConfigDataScope
    {
        $this->entity = $request[ConfigScopeInterface::REQUEST_TYPE_ID] ?? null;
        $this->profileId = isset($request[ConfigScopeInterface::REQUEST_ID])
            ? (int) $request[ConfigScopeInterface::REQUEST_ID]
            : null;
        $this->scope = isset($request[StoreScopeInterface::SCOPE_WEBSITE])
            ? StoreScopeInterface::SCOPE_WEBSITE
            : (isset($request[StoreScopeInterface::SCOPE_STORE])
                ? StoreScopeInterface::SCOPE_STORE
                : ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->scopeId = $request[StoreScopeInterface::SCOPE_WEBSITE]
            ?? ($request[StoreScopeInterface::SCOPE_STORE] ?? null);

        return $this;
    }
}
