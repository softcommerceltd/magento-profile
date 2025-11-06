<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use SoftCommerce\Profile\Api\Data\ConfigInterface;
use SoftCommerce\Profile\Api\Data\ConfigSearchResultsInterface;
use SoftCommerce\Profile\Api\Data\ConfigSearchResultsInterfaceFactory;
use SoftCommerce\Profile\Api\ConfigRepositoryInterface;
use SoftCommerce\Profile\Model\ResourceModel\Config as ConfigResource;
use SoftCommerce\Profile\Model\ResourceModel\Config\CollectionFactory;

/**
 * @inheritDoc
 */
class ConfigRepository implements ConfigRepositoryInterface
{
    /**
     * @param ConfigResource $resource
     * @param CollectionFactory $collectionFactory
     * @param ConfigFactory $modelFactory
     * @param ConfigSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        private readonly ConfigResource $resource,
        private readonly CollectionFactory $collectionFactory,
        private readonly ConfigFactory $modelFactory,
        private readonly ConfigSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ConfigSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var ConfigSearchResultsInterface $searchResults */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param int $entityId
     * @param string|null $field
     * @return ConfigInterface|Config
     * @throws NoSuchEntityException
     */
    public function get(int $entityId, ?string $field = null)
    {
        /** @var ConfigInterface|Config $model */
        $model = $this->modelFactory->create();
        $this->resource->load($model, $entityId, $field);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('The config with ID "%1" doesn\'t exist.', $entityId));
        }

        return $model;
    }

    /**
     * @param ConfigInterface|Config $config
     * @return ConfigInterface
     * @throws CouldNotSaveException
     */
    public function save(ConfigInterface $config)
    {
        try {
            $this->resource->save($config);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $config;
    }

    /**
     * @param ConfigInterface|Config $config
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ConfigInterface $config)
    {
        try {
            $this->resource->delete($config);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId)
    {
        return $this->delete($this->get($entityId));
    }
}
