<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\DataProvider\Profile\Form\Modifier;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use SoftCommerce\Profile\Model\RegistryLocatorInterface;
use SoftCommerce\Profile\Ui\DataProvider\Modifier\Form\MetadataPoolInterface;
use SoftCommerce\Profile\Ui\DataProvider\Profile\Config\Form\ConfigDataScopeInterface;
use SoftCommerce\Profile\Ui\DataProvider\Profile\Config\Form\ConfigDataScopeInterfaceFactory;
use SoftCommerce\ProfileConfig\Model\ConfigScopeInterface;

/**
 * @inheritDoc
 */
class ProfileConfigData extends AbstractModifier implements ModifierInterface
{
    const DATA_SOURCE = 'profile_config';
    const FORM_PREFIX = 'profile_';
    const FORM_SUFFIX = '_form';
    const DATA_SOURCE_COMPONENT_TEMPLATE = 'SoftCommerce_Profile/js/form/element/single-checkbox-use-default';
    const DATA_SOURCE_SCOPE_TEMPLATE = 'SoftCommerce_Profile/form/element/helper/service';

    /**
     * @var ConfigDataScopeInterface
     */
    private $configScope;

    /**
     * @var array
     */
    private $meta;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $serializedData;

    /**
     * @param ConfigDataScopeInterfaceFactory $configScopeFactory
     * @param ArrayManager $arrayManager
     * @param RequestInterface $request
     * @param RegistryLocatorInterface $registryLocator
     * @param MetadataPoolInterface $metadataPool
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ConfigDataScopeInterfaceFactory $configScopeFactory,
        ArrayManager $arrayManager,
        RequestInterface $request,
        RegistryLocatorInterface $registryLocator,
        MetadataPoolInterface $metadataPool,
        SerializerInterface $serializer
    ) {
        $this->configScope = $configScopeFactory->create(['data' => $request->getParams()]);
        $this->serializer = $serializer;
        parent::__construct($arrayManager, $request, $registryLocator, $metadataPool);
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function modifyData(array $data)
    {
        $model = $this->registryLocator->getProfile();
        $configData = $this->configScope->get();
        $useDefault = [];
        foreach ($this->serializedData ?: [] as $path => $state) {
            $path = explode('/', $path);
            array_shift($path);
            if (count($path) <> 2) {
                continue;
            }

            $containerIndex = current($path);
            $fieldIndex = end($path);
            if (!isset($configData[$containerIndex][$fieldIndex])) {
                continue;
            }

            if (!$this->configScope->isCurrentScopeDefault()) {
                $useDefault[self::DATA_SOURCE][$containerIndex][$fieldIndex] = 0;
            }

            if (false === $state) {
                $configData[$containerIndex][$fieldIndex] = [];
                continue;
            }

            try {
                $value = $this->serializer->unserialize($configData[$containerIndex][$fieldIndex]);
            } catch (\InvalidArgumentException $e) {
                $value = [];
            }

            $configData[$containerIndex][$fieldIndex] = $value;
        }

        $data[$model->getEntityId()][self::DATA_SOURCE] = $configData;

        if ($useDefault) {
            $data[$model->getEntityId()]['use_default'] = $useDefault;
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @throws \Exception
     */
    public function modifyMeta(array $meta)
    {
        return $this->generateMetaData($meta);
    }

    /**
     * @param array $meta
     * @return array
     * @throws \Exception
     */
    private function generateMetaData(array $meta): array
    {
        $this->meta = $meta;
        $requestParams = $this->request->getParams();
        $entity = $requestParams[ConfigScopeInterface::REQUEST_TYPE_ID] ?? null;
        $componentName = self::FORM_PREFIX . $entity . self::FORM_SUFFIX;
        $metaData = $this->metadataPool->get($componentName);
        $metaData = $metaData['children'][self::DATA_SOURCE]['children'] ?? [];

        foreach ($metaData as $fieldSetName => $fieldSet) {
            foreach ($fieldSet['children'] ?? [] as $fieldName => $field) {
                $items = $field['arguments']['data']['item']['config']['item'] ?? [];
                $configPath = $entity . '/' . $fieldSetName . '/' . $fieldName;

                $this->generateScopedMetaData($items, $fieldSetName, $fieldName);
                $this->collectSerializedData($items, $configPath);
            }
        }

        return $this->meta;
    }

    /**
     * @param array $data
     * @param string $fieldSetName
     * @param string $fieldName
     * @return void
     * @throws \Exception
     */
    private function generateScopedMetaData(
        array $data,
        string $fieldSetName,
        string $fieldName
    ): void {
        $entity = $this->getEntityTypeValueFromRequest();
        if ($this->configScope->isCurrentScopeDefault()
            || !$entity
            || !$scope = $this->parseScopeCode($data)
        ) {
            return;
        }

        $configPath = $entity . '/' . $fieldSetName . '/' . $fieldName;
        $isDisabled = $this->configScope->isDefaultValue($configPath);
        $isDynamicRows = isset($data['componentType']['value']) && $data['componentType']['value'] === 'dynamicRows';
        $itemConfig = [];
        foreach ($data as $index => $item) {
            if (!$this->getScopeValueFromRequest($scope)) {
                $itemConfig['visible'] = false;
                continue;
            }

            if ($isDynamicRows) {
                continue;
            }

            if (isset($item['value'])) {
                $itemConfig[$index] = $item['value'];
            }

            if (isset($item['item']['required-entry'])) {
                $itemConfig['required'] = 1;
                $itemConfig[$index]['required-entry'] = 1;
            }

            if (in_array($scope, [StoreScopeInterface::SCOPE_STORE, StoreScopeInterface::SCOPE_WEBSITE])) {
                $itemConfig['disabled'] = $isDisabled;
            }

            $itemConfig['component'] = self::DATA_SOURCE_COMPONENT_TEMPLATE;
            $itemConfig['service']['template'] = self::DATA_SOURCE_SCOPE_TEMPLATE;
        }

        if ($itemConfig) {
            $this->meta[self::DATA_SOURCE]['children'][$fieldSetName]['children'][$fieldName]['arguments']['data']
            ['config'] = $itemConfig;
        }
    }

    /**
     * @param array $data
     * @param $configPath
     * @return void
     * @throws \Exception
     */
    private function collectSerializedData(array $data, $configPath): void
    {
        if (isset($data['componentType']['value']) && $data['componentType']['value'] === 'dynamicRows') {
            $this->serializedData[$configPath] = $this->configScope->isCurrentScopeDefault()
                || ($this->getScopeValueFromRequest($this->parseScopeCode($data))
                    && !$this->configScope->isDefaultValue($configPath)
                );
        }

        if (isset($data['isDataSerialized']['value']) && false !== $data['isDataSerialized']['value']) {
            $this->serializedData[$configPath] = true;
        }
    }

    /**
     * @param array $data
     * @return string
     */
    private function parseScopeCode(array $data): string
    {
        return str_replace(['[', ']'], '', $data['scopeLabel']['value'] ?? '');
    }

    /**
     * @return string|null
     */
    private function getEntityTypeValueFromRequest(): ?string
    {
        return $this->request->getParam(ConfigScopeInterface::REQUEST_TYPE_ID);
    }

    /**
     * @param string $scopeCode
     * @return int
     */
    private function getScopeValueFromRequest(string $scopeCode): int
    {
        return (int) $this->request->getParam($scopeCode, 0);
    }
}
