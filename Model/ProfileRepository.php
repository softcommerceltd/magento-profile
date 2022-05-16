<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

use Magento\Framework\Api\SearchResults;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Profile\Api\Data\Profile\SearchResultsInterfaceFactory;
use SoftCommerce\Profile\Api\ProfileRepositoryInterface;
use SoftCommerce\Profile\Api\Data\Profile\SearchResultsInterface;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Model\ResourceModel;

/**
 * @inheritDoc
 */
class ProfileRepository implements ProfileRepositoryInterface
{
    /**
     * @var ResourceModel\Profile
     */
    private $resource;

    /**
     * @var ProfileFactory
     */
    private $profileFactory;

    /**
     * @var ResourceModel\Profile\CollectionFactory
     */
    private $profileCollectionFactory;

    /**
     * @var SearchResultsInterface|SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourceModel\Profile $resource
     * @param ProfileFactory $profileFactory
     * @param ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceModel\Profile $resource,
        ProfileFactory $profileFactory,
        ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->profileFactory = $profileFactory;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\Profile\SearchResultsInterface|SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceModel\Profile\Collection $collection */
        $collection = $this->profileCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var Data\Profile\SearchResultsInterface|SearchResults $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param int $entityId
     * @param string|null $field
     * @return ProfileInterface
     * @throws NoSuchEntityException
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
     * @param int $profileId
     * @return ProfileInterface
     * @throws NoSuchEntityException
     */
    public function getById($profileId)
    {
        return $this->get($profileId, ProfileInterface::ENTITY_ID);
    }

    /**
     * @param string $entityType
     * @param string $adaptor
     * @return ProfileInterface|Profile
     * @throws LocalizedException
     * @throws NoSuchEntityException
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
     * @param string $entityType
     * @param string $adaptor
     * @return int|mixed|null
     * @throws LocalizedException
     */
    public function getIdByEntity($entityType, $adaptor)
    {
        $profileId = $this->resource->getProfileByEntityType($entityType, $adaptor, ProfileInterface::ENTITY_ID);
        $profileId = current($profileId);

        return $profileId[ProfileInterface::ENTITY_ID] ?? null;
    }

    /**
     * @param ProfileInterface|Profile $profile
     * @return ProfileInterface|Profile
     * @throws CouldNotSaveException
     * @throws LocalizedException
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
     * @param ProfileInterface|Profile $profile
     * @return bool
     * @throws CouldNotDeleteException
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
     * @param int $profileId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($profileId)
    {
        return $this->delete($this->getById($profileId));
    }
}
