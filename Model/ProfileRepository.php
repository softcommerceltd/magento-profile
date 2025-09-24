<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use SoftCommerce\Profile\Api\Data\Profile\SearchResultsInterfaceFactory;
use SoftCommerce\Profile\Api\ProfileRepositoryInterface;
use SoftCommerce\Profile\Api\Data\ProfileInterface;

/**
 * @inheritDoc
 */
class ProfileRepository implements ProfileRepositoryInterface
{
    /**
     * @param ResourceModel\Profile $resource
     * @param ProfileFactory $profileFactory
     * @param ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        private ResourceModel\Profile $resource,
        private ProfileFactory $profileFactory,
        private ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        private SearchResultsInterfaceFactory $searchResultsFactory,
        private CollectionProcessorInterface $collectionProcessor
    ) {}

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceModel\Profile\Collection $collection */
        $collection = $this->profileCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function get($entityId, $field = null)
    {
        /** @var ProfileInterface|Profile $profile */
        $profile = $this->profileFactory->create();
        $this->resource->load($profile, $entityId, $field);
        if (!$profile->getId()) {
            throw new NoSuchEntityException(__('Profile with ID "%1" doesn\'t exist.', $entityId));
        }

        return $profile;
    }

    /**
     * @inheritDoc
     */
    public function getById($profileId)
    {
        return $this->get($profileId, ProfileInterface::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function getByEntity($entityType, $adaptor)
    {
        $profileId = $this->resource->getProfileByEntityType($entityType, $adaptor, ProfileInterface::ENTITY_ID);
        $profileId = current($profileId);
        if (!isset($profileId[ProfileInterface::ENTITY_ID])
            || !$profileId = $profileId[ProfileInterface::ENTITY_ID]
        ) {
            throw new NoSuchEntityException(
                __('Profile with entity "%1" and adaptor "%2" does not exist.', $entityType, $adaptor)
            );
        }

        return $this->get($profileId, ProfileInterface::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function getIdByEntity($entityType, $adaptor)
    {
        $profileId = $this->resource->getProfileByEntityType($entityType, $adaptor, ProfileInterface::ENTITY_ID);
        $profileId = current($profileId);

        return $profileId[ProfileInterface::ENTITY_ID] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function save(ProfileInterface $profile)
    {
        try {
            $this->resource->save($profile);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $profile;
    }

    /**
     * @inheritDoc
     */
    public function delete(ProfileInterface $profile)
    {
        try {
            $this->resource->delete($profile);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($profileId)
    {
        return $this->delete($this->getById($profileId));
    }
}
