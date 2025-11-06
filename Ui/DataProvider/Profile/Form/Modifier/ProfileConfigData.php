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
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use SoftCommerce\Profile\Model\RegistryLocatorInterface;
use SoftCommerce\Profile\Ui\DataProvider\Modifier\Form\MetadataPoolInterface;
use SoftCommerce\Profile\Ui\DataProvider\Profile\Config\Form\ConfigDataScopeInterface;
use SoftCommerce\Profile\Ui\DataProvider\Profile\Config\Form\ConfigDataScopeInterfaceFactory;
use SoftCommerce\Profile\Model\Config\ConfigScopeInterface;

/**
 * @inheritDoc
 */
class ProfileConfigData extends AbstractModifier implements ModifierInterface
{
    public const DATA_SOURCE = 'profile_config';
    public const FORM_PREFIX = 'profile_';
    public const FORM_SUFFIX = '_form';
    public const DATA_SOURCE_COMPONENT_TEMPLATE = 'SoftCommerce_Profile/js/form/element/single-checkbox-use-default';
    public const DATA_SOURCE_SCOPE_TEMPLATE = 'SoftCommerce_Profile/form/element/helper/service';

    /**
     * @var ConfigDataScopeInterface
     */
    private ConfigDataScopeInterface $configScope;

    /**
     * @var string
     */
    private string $dataSource = 'profile_config';

    /**
     * @var array
     */
    private array $meta;

    /**
     * @var string|null
     */
    private ?string $profileTypeId = null;

    /**
     * @var array
     */
    private array $modifyDataRequest = [];

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
        private readonly SerializerInterface $serializer
    ) {
        $this->configScope = $configScopeFactory->create(['data' => $request->getParams()]);
        parent::__construct($arrayManager, $request, $registryLocator, $metadataPool);
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data): array
    {
        $model = $this->registryLocator->getProfile();
        $configData = $this->configScope->get();
        $useDefault = [];

        foreach ($this->modifyDataRequest ?: [] as $path => $item) {
            $path = explode('/', $path);
            array_shift($path);

            if (count($path) != 2) {
                continue;
            }

            $containerIndex = current($path);
            $fieldIndex = end($path);

            if (!isset($configData[$containerIndex][$fieldIndex])) {
                continue;
            }

            if (isset($item['use_default'])) {
                $useDefault[self::DATA_SOURCE][$containerIndex][$fieldIndex] = $item['use_default'];
            }

            if (!isset($item['serialized']) || false === $item['serialized']) {
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
     * @inheritDoc
     * @throws \Exception
     */
    public function modifyMeta(array $meta): array
    {
        $this->meta = $meta;
        $requestParams = $this->request->getParams();
        $this->profileTypeId = $requestParams[ConfigScopeInterface::REQUEST_TYPE_ID] ?? null;
        $componentName = self::FORM_PREFIX . $this->profileTypeId . self::FORM_SUFFIX;
        $metaData = $this->metadataPool->get($componentName);
        $metaData = $metaData['children'][self::DATA_SOURCE]['children'] ?? [];

        foreach ($metaData as $fieldSetName => $data) {
            foreach ($data['children'] ?? [] as $fieldName => $items) {
                $this->processModifyDataComponent($fieldSetName, $fieldName, $items);
            }
        }

        return $this->meta;
    }

    /**
     * @param string $fieldSetName
     * @param string $fieldName
     * @param array $items
     * @return void
     * @throws \Exception
     */
    private function processModifyDataComponent(string $fieldSetName, string $fieldName, array $items): void
    {
        if (!$this->canProcessModifyDataComponent($items)) {
            return;
        }

        $componentType = $this->retrieveComponentType($items);

        if ($componentType === 'group') {
            $this->processModifyDataComponentGroup($items['children'] ?? [], $fieldSetName, $fieldName);
        } else {
            $this->processModifyDataComponentGroup([$fieldName => $items], $fieldSetName);
        }
    }

    /**
     * @param array $items
     * @param string $fieldSetName
     * @param string|null $groupName
     * @return void
     * @throws \Exception
     */
    private function processModifyDataComponentGroup(
        array $items,
        string $fieldSetName,
        ?string $groupName = null
    ): void
    {
        foreach ($items as $fieldName => $fieldItems) {
            $configData = $this->retrieveItemConfig($fieldItems);
            $configPath = $this->getConfigPath($fieldSetName, $fieldName);

            if (!$this->canProcessModifyDataComponent($fieldItems)) {
                continue;
            }

            $configMetadata = $this->generateScopedMetaData($fieldItems, $fieldSetName, $fieldName, $groupName);

            if ($configMetadata) {
                $groupPath = $groupName ? "$groupName/children/" : null;
                $path = "$this->dataSource/children/$fieldSetName/children/$groupPath$fieldName/arguments/data/config";
                if ($this->arrayManager->exists($path, $this->meta)) {
                    $this->meta = $this->arrayManager->merge(
                        $path,
                        $this->meta,
                        $configMetadata
                    );
                } else {
                    $this->meta = $this->arrayManager->set(
                        $path,
                        $this->meta,
                        $configMetadata
                    );
                }
            }

            $this->generateDataModifyRequest($configData, $configPath);
        }
    }

    /**
     * @param array $data
     * @param string $fieldSetName
     * @param string $fieldName
     * @param string|null $groupName
     * @return array
     * @throws \Exception
     */
    private function generateScopedMetaData(
        array $data,
        string $fieldSetName,
        string $fieldName,
        ?string $groupName = null
    ): array
    {
        $configData = $this->retrieveItemConfig($data);

        if ($this->configScope->isCurrentScopeDefault()
            || !$scope = $this->parseScopeCode($configData)
        ) {
            return [];
        }

        $configPath = $this->getConfigPath($fieldSetName, $fieldName);
        $componentType = $this->retrieveComponentType($data);
        $isDynamicRows = $componentType === 'dynamicRows';
        $itemConfig = [];

        if ($scope !== $this->configScope->getCurrentScope()) {
            $itemConfig['disabled'] = true;

            if ($isDynamicRows) {
                $this->disableChildComponents($data, $fieldSetName, $fieldName, $groupName);
            }

            return $itemConfig;
        }

        foreach ($configData as $index => $item) {
            if (isset($item['value'])) {
                $value = $item['value'];

                if (($item['xsi:type'] ?? null) === 'boolean') {
                    $value = !($value === 'false');
                }

                $itemConfig[$index] = $value;
            }

            if (isset($item['item']['required-entry'])) {
                $itemConfig['required'] = 1;
                $itemConfig[$index]['required-entry'] = 1;
            }
        }

        $isDisabled = $this->configScope->isDefaultValue($configPath);
        $itemConfig['disabled'] = $isDisabled;
        $itemConfig['service']['template'] = 'ui/form/element/helper/service';

        if ($isDynamicRows && $isDisabled) {
            $this->disableChildComponents($data, $fieldSetName, $fieldName, $groupName);
        }

        return $itemConfig;
    }

    /**
     * @param array $data
     * @param $configPath
     * @return void
     * @throws \Exception
     */
    private function generateDataModifyRequest(array $data, $configPath): void
    {
        $scopeLabel = $this->parseScopeCode($data);

        if (($data['componentType']['value'] ?? null) === 'dynamicRows'
            || (isset($data['isDataSerialized']['value']) && $data['isDataSerialized']['value'] !== 'false')
        ) {
            $this->modifyDataRequest[$configPath]['serialized'] = true;
        }

        if ($scopeLabel === $this->configScope->getCurrentScope()) {
            $this->modifyDataRequest[$configPath]['use_default'] = $this->configScope->isCurrentScopeDefault()
                || ($this->getScopeValueFromRequest($scopeLabel) && !$this->configScope->isDefaultValue($configPath));
        }
    }

    /**
     * @param array $data
     * @param string $fieldSetName
     * @param string $fieldName
     * @param string|null $groupName
     * @return void
     */
    private function disableChildComponents(
        array $data,
        string $fieldSetName,
        string $fieldName,
        ?string $groupName = null
    ): void
    {
        foreach ($data['children']['record']['children'] ?? [] as $itemFieldName => $itemData) {
            $config = [];
            foreach ($this->retrieveItemConfig($itemData) as $index => $item) {
                if (isset($item['value'])) {
                    $value = $item['value'];

                    if (($item['xsi:type'] ?? null) === 'boolean') {
                        $value = !($value === 'false');
                    }

                    $config[$index] = $value;
                }

                if (isset($item['item']['required-entry'])) {
                    $config['required'] = 1;
                    $config[$index]['required-entry'] = 1;
                }

                $config['disabled'] = 1;
            }

            $path = [self::DATA_SOURCE, 'children', $fieldSetName, 'children', $fieldName, 'children'];

            if (null !== $groupName) {
                array_push($path, $fieldName, 'children');
            }

            array_push($path, $itemFieldName, 'arguments', 'data', 'config');
            $path = implode('/', $path);

            if ($this->arrayManager->exists($path, $this->meta)) {
                $this->meta = $this->arrayManager->merge($path, $this->meta, $config);
            } else {
                $this->meta = $this->arrayManager->set($path, $this->meta, $config);
            }
        }
    }

    /**
     * @param array $data
     * @param string|null $index
     * @return array|string|null
     */
    private function retrieveItemConfig(array $data, ?string $index = null): array|string|null
    {
        return null !== $index
            ? ($data['arguments']['data']['item']['config']['item'][$index]['value'] ?? null)
            : ($data['arguments']['data']['item']['config']['item'] ?? []);
    }

    /**
     * @param array $data
     * @return string|null
     */
    private function retrieveComponentName(array $data): ?string
    {
        return $data['component']['value'] ?? null;
    }

    /**
     * @param array $data
     * @return string|null
     */
    private function retrieveComponentTemplate(array $data): ?string
    {
        return $data['template']['value'] ?? null;
    }

    /**
     * @param array $data
     * @return string|null
     */
    private function retrieveComponentType(array $data): ?string
    {
        return $data['arguments']['data']['item']['type']['value']
            ?? $this->retrieveItemConfig($data, 'componentType');
    }

    /**
     * @param array $data
     * @return string|null
     */
    private function retrieveFormElementName(array $data): ?string
    {
        return $data['formElement']['value'] ?? null;
    }

    /**
     * @param string $fieldSetName
     * @param string $fieldName
     * @return string
     */
    private function getConfigPath(string $fieldSetName, string $fieldName): string
    {
        return "{$this->profileTypeId}/$fieldSetName/$fieldName";
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

    /**
     * @param array $data
     * @return string
     */
    private function parseScopeCode(array $data): string
    {
        return str_replace(['[', ']'], '', $data['scopeLabel']['value'] ?? '');
    }

    /**
     * @param array $componentData
     * @return bool
     */
    private function canProcessModifyDataComponent(array $componentData): bool
    {
        $configData = $this->retrieveItemConfig($componentData);
        $componentName = $this->retrieveComponentName($configData);
        $componentTemplate = $this->retrieveComponentTemplate($configData);

        if ($componentTemplate === 'ui/form/components/button/container') {
            return false;
        }

        if (in_array(
            $componentName,
            [
                'Magento_Ui/js/modal/modal-component',
                'SoftCommerce_PlentyProfile/js/components/create-external-entity-button'
            ]
        )) {
            return false;
        }

        return true;
    }
}
