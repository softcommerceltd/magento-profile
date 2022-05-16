<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ServiceAbstract;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Core\Framework\DataStorageInterface;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterface;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use function array_column;
use function usort;

/**
 * Class Service used to provide
 * wrapper class for profile service management.
 */
abstract class Service
{
    /**
     * @var ServiceInterface|null
     */
    protected $context;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var DataStorageInterfaceFactory
     */
    protected $dataStorageFactory;

    /**
     * @var MessageStorageInterfaceFactory
     */
    protected $messageStorageFactory;

    /**
     * @var DataStorageInterface
     */
    private $dataStorage;

    /**
     * @var DataStorageInterface
     */
    private $responseStorage;

    /**
     * @var DataStorageInterface
     */
    protected $requestStorage;

    /**
     * @var MessageStorageInterface
     */
    protected $messageStorage;

    /**
     * @var int|null
     */
    protected $profileId;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var array
     */
    protected $request;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var string|null
     */
    protected $typeId;

    /**
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->dataStorageFactory = $dataStorageFactory;
        $this->messageStorageFactory = $messageStorageFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->data = $data;
        $this->dataStorage = $this->dataStorageFactory->create();
        $this->requestStorage = $this->dataStorageFactory->create();
        $this->responseStorage = $this->dataStorageFactory->create();
        $this->messageStorage = $this->messageStorageFactory->create();
        $this->profileId = $data[ProfileInterface::PROFILE_ID] ?? null;
    }

    /**
     * @return $this
     */
    public function initialize()
    {
        $this->request =
        $this->response =
            [];
        $this->dataStorage->resetData();
        $this->requestStorage->resetData();
        $this->responseStorage->resetData();
        $this->messageStorage->resetData();
        return $this;
    }

    /**
     * @return $this
     */
    public function finalize()
    {
        return $this;
    }

    /**
     * @return ServiceInterface|null
     * @throws LocalizedException
     */
    public function getContext()
    {
        if (null === $this->context) {
            throw new LocalizedException(__('Context object is not set.'));
        }

        return $this->context;
    }

    /**
     * @param $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return DataStorageInterface
     */
    public function getDataStorage(): DataStorageInterface
    {
        return $this->dataStorage;
    }

    /**
     * @return DataStorageInterface
     */
    public function getRequestStorage(): DataStorageInterface
    {
        return $this->requestStorage;
    }

    /**
     * @return DataStorageInterface
     */
    public function getResponseStorage(): DataStorageInterface
    {
        return $this->responseStorage;
    }

    /**
     * @return MessageStorageInterface
     */
    public function getMessageStorage(): MessageStorageInterface
    {
        return $this->messageStorage;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getProfileId(): int
    {
        if (!$this->profileId) {
            throw new LocalizedException(__('Profile ID is not set.'));
        }
        return (int) $this->profileId;
    }

    /**
     * @param int $profileId
     * @return $this
     */
    public function setProfileId(int $profileId)
    {
        $this->profileId = $profileId;
        return $this;
    }

    /**
     * @param $key
     * @return array|mixed|null[]
     */
    protected function getData($key = null)
    {
        return null !== $key
            ? ($this->data[$key] ?? null)
            : ($this->data ?: []);
    }

    /**
     * @param array|string|mixed $data
     * @param int|string|null $key
     * @return $this
     */
    public function setData($data, $key = null)
    {
        if (null !== $key) {
            $this->data[$key] = $data;
        } else {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTypeId(): ?string
    {
        return $this->typeId;
    }

    /**
     * @param ServiceInterface $context
     * @return $this
     */
    public function init($context)
    {
        $this->context = $context;
        $this->setData($context->getData());
        return $this;
    }

    /**
     * @param ServiceInterface $context
     * @param ServiceInterface[] $instances
     */
    protected function initTypeInstances($context, array $instances)
    {
        $this->context = $context;
        foreach ($instances as $instance) {
            $instance->init($context);
        }
    }

    /**
     * @param array $services
     * @return array
     */
    protected function initServices(array $services): array
    {
        usort($services, function (array $a, array $b) {
            return $this->getSortOrder($a) <=> $this->getSortOrder($b);
        });

        return array_column($services, 'class');
    }

    /**
     * @param array $item
     * @return int
     */
    private function getSortOrder(array $item): int
    {
        return (int) ($item['sortOrder'] ?? 0);
    }
}
