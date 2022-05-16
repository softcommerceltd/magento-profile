<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

use Magento\Framework\Serialize\SerializerInterface;
use SoftCommerce\Core\Framework\DataStorageInterface;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterface;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Core\Model\Config\DateTimeLocaleInterface;

/**
 * Class AbstractManagement used to provide
 * wrapper class for data entity management.
 */
abstract class AbstractManagement
{
    /**
     * @var DataStorageInterfaceFactory
     */
    protected $dataStorageFactory;

    /**
     * @var DataStorageInterfaceFactory
     */
    protected $messageStorageFactory;

    /**
     * @var DataStorageInterface
     */
    protected $dataStorage;

    /**
     * @var DateTimeLocaleInterface
     */
    protected $dateTimeLocale;

    /**
     * @var array
     */
    protected $clientResponse;

    /**
     * @var array
     */
    protected $collectionResult;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var MessageStorageInterface
     */
    protected $responseStorage;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $searchCriteria;

    /**
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param DateTimeLocaleInterface $dateTimeLocale
     * @param SerializerInterface $serializer
     */
    public function __construct(
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        DateTimeLocaleInterface $dateTimeLocale,
        SerializerInterface $serializer
    ) {
        $this->dataStorageFactory = $dataStorageFactory;
        $this->messageStorageFactory = $messageStorageFactory;
        $this->dateTimeLocale = $dateTimeLocale;
        $this->serializer = $serializer;
        $this->dataStorage = $this->dataStorageFactory->create();
        $this->responseStorage = $this->messageStorageFactory->create();
    }

    /**
     * @return DataStorageInterface
     */
    public function getDataStorage(): DataStorageInterface
    {
        return $this->dataStorage;
    }

    /**
     * @return MessageStorageInterface
     */
    public function getResponseStorage(): MessageStorageInterface
    {
        return $this->responseStorage;
    }

    /**
     * @param array $searchParams
     * @return $this
     */
    public function setSearchCriteria(array $searchParams)
    {
        $this->searchCriteria = $searchParams;
        return $this;
    }

    /**
     * @return array
     */
    protected function getSearchCriteria(): array
    {
        return $this->searchCriteria ?: [];
    }

    /**
     * @param int|string|null $key
     * @return array|mixed|null
     */
    public function getResponse($key = null)
    {
        return null !== $key
            ? ($this->response[$key] ?? null)
            : $this->response;
    }

    /**
     * @param string|array $data
     * @param null|string|int $key
     * @return $this
     */
    protected function setResponse($data, $key = null)
    {
        null !== $key
            ? $this->response[$key] = $data
            : $this->response = $data;
        return $this;
    }

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    protected function addResponse($data, $key = null)
    {
        null !== $key
            ? $this->response[$key][] = $data
            : $this->response[] = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getCollectionResult(): array
    {
        return $this->collectionResult ?: [];
    }

    /**
     * @param array $data
     * @param array $metadata
     * @return array
     */
    public function resolveMetaDataMapping(array $data, array $metadata)
    {
        return array_filter($data, function ($key) use ($metadata) {
            return in_array($key, $metadata);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array $entityIds
     * @return $this
     */
    protected function setCollectionResult(array $entityIds)
    {
        $this->collectionResult = $entityIds;
        return $this;
    }

    /**
     * @param int $entityId
     * @return $this
     */
    protected function addCollectionResult(int $entityId)
    {
        $this->collectionResult[] = $entityId;
        return $this;
    }

    /**
     * @return $this
     */
    protected function collectBefore()
    {
        $this->response =
        $this->collectionResult =
            [];
        $this->getDataStorage()->resetData();
        $this->getResponseStorage()->resetData();
        return $this;
    }

    /**
     * @param string $callBackName
     * @return string
     */
    protected function buildSearchCriteriaMethodName(string $callBackName): string
    {
        return 'setFilter' . str_replace('_', '', ucwords($callBackName, '_'));
    }

    /**
     * @param $needle
     * @param array $haystack
     * @param $columnName
     * @param null $columnId
     * @return false|int|string
     */
    protected function getSearchArrayMatch(
        $needle,
        array $haystack,
        $columnName,
        $columnId = null
    ) {
        return array_search($needle, array_column($haystack, $columnName, $columnId));
    }

    /**
     * @param array $item
     * @return array|bool[]|string[]
     */
    protected function buildDataForSave(array $item)
    {
        return array_map(
            function ($element) {
                if (is_array($element)) {
                    try {
                        $element = $this->serializer->serialize($element);
                    } catch (\InvalidArgumentException $e) {
                        $element = $e->getMessage();
                    }
                }
                return $element;
            },
            $item
        );
    }
}
