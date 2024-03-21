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
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Ui\DataProvider\Profile\Form\Modifier\ProfileConfigData;
use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;
use SoftCommerce\ProfileConfig\Model\ConfigScopeInterface;
use SoftCommerce\ProfileConfig\Model\ResourceModel;

/**
 * @inheritDoc
 */
class ConfigDataScopeStorage implements ConfigDataScopeStorageInterface
{
    /**
     * @var ConfigScopeInterface
     */
    private ConfigScopeInterface $configScope;

    /**
     * @var ResourceModel\Config
     */
    private ResourceModel\Config $resource;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param ConfigScopeInterface $configScope
     * @param ResourceModel\Config $resource
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ConfigScopeInterface $configScope,
        ResourceModel\Config $resource,
        SerializerInterface $serializer
    ) {
        $this->configScope = $configScope;
        $this->resource = $resource;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function saveFormData(array $request): array
    {
        $profileId = (int) ($request['id'] ?? null);
        if (!$profileId || !$typeId = $request[ProfileInterface::TYPE_ID] ?? null) {
            return [];
        }

        $scope = $this->getScope($request);
        $scopeId = $this->getScopeId($request);
        $requestData = $this->prepareRequestData($request, $scope);

        $saveRequest = [];
        foreach ($requestData as $group => $attributes) {
            if (!is_array($attributes)) {
                continue;
            }

            foreach ($attributes as $attribute => $value) {
                if (null === $value || '' === $value) {
                    continue;
                }

                $path = "$typeId/$group/$attribute";
                if (is_array($value)) {
                    try {
                        $value = $this->serializer->serialize($value);
                    } catch (\InvalidArgumentException $e) {
                        $value = $e->getMessage();
                    }
                }

                $saveRequest[] = [
                    ConfigInterface::PARENT_ID => $profileId,
                    ConfigInterface::SCOPE => $scope,
                    ConfigInterface::SCOPE_ID => $scopeId,
                    ConfigInterface::PATH => $path,
                    ConfigInterface::VALUE => $value
                ];
            }
        }

        $this->resource->clearScopeData($profileId, $scope, $scopeId);
        if ($saveRequest) {
            $this->resource->insertOnDuplicate($saveRequest);
        }
        $this->configScope->clean();

        return $saveRequest;
    }

    /**
     * @param array $request
     * @param string $scope
     * @return array
     */
    private function prepareRequestData(array $request, string $scope): array
    {
        $requestData = $request[ProfileConfigData::DATA_SOURCE] ?? [];
        if (!$requestData || $scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            return $requestData;
        }

        $response = [];
        foreach ($request['use_default'][ProfileConfigData::DATA_SOURCE] ?? [] as $fieldsetPath => $fieldPaths) {
            if (!is_array($fieldPaths)) {
                continue;
            }

            foreach ($fieldPaths as $fieldPath => $canUseDefaultValue) {
                if (is_string($canUseDefaultValue)) {
                    $canUseDefaultValue = !($canUseDefaultValue === 'false' || $canUseDefaultValue === '0');
                }

                if (isset($requestData[$fieldsetPath][$fieldPath]) && !$canUseDefaultValue) {
                    $response[$fieldsetPath][$fieldPath] = $requestData[$fieldsetPath][$fieldPath];
                }
            }
        }

        return $response;
    }

    /**
     * @param array $request
     * @return string
     */
    private function getScope(array $request): string
    {
        return isset($request[StoreScopeInterface::SCOPE_WEBSITE])
            ? StoreScopeInterface::SCOPE_WEBSITES
            : (
                isset($request[StoreScopeInterface::SCOPE_STORE])
                    ? StoreScopeInterface::SCOPE_STORES
                    : ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
    }

    /**
     * @param array $request
     * @return int|string
     */
    private function getScopeId(array $request): int|string
    {
        return $request[StoreScopeInterface::SCOPE_WEBSITE]
            ?? ($request[StoreScopeInterface::SCOPE_STORE] ?? 0);
    }
}
