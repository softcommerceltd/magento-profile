<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use SoftCommerce\Profile\Model\Profile;

/**
 * Interface ProfileRepositoryInterface
 */
interface ProfileRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\Profile\SearchResultsInterface|SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $entityId
     * @param string|null $field
     * @return Data\ProfileInterface
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null);

    /**
     * @param int $profileId
     * @return Data\ProfileInterface
     * @throws NoSuchEntityException
     */
    public function getById($profileId);

    /**
     * @param string $entityType
     * @param string $adaptor
     * @return Data\ProfileInterface|Profile
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getByEntity($entityType, $adaptor);

    /**
     * @param string $entityType
     * @param string $adaptor
     * @return int|mixed|null
     * @throws LocalizedException
     */
    public function getIdByEntity($entityType, $adaptor);

    /**
     * @param Data\ProfileInterface|Profile $profile
     * @return Data\ProfileInterface|Profile
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function save(Data\ProfileInterface $profile);

    /**
     * @param Data\ProfileInterface|Profile $profile
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\ProfileInterface $profile);

    /**
     * @param int $profileId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($profileId);
}
