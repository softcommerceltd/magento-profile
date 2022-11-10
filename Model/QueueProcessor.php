<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Core\Framework\MessageStorage\StatusPredictionInterface;
use SoftCommerce\Core\Framework\MessageStorageInterface;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Core\Model\Source\Status;
use SoftCommerce\ProfileHistory\Api\HistoryManagementInterface;

/**
 * @inheritDoc
 * @deprecated
 */
class QueueProcessor extends QueueProcessor\AbstractProcessor implements QueueProcessorInterface
{
    /**
     * @var ProfileEntityInterface|null
     */
    private $profileEntity;

    /**
     * @var HistoryManagementInterface
     */
    private HistoryManagementInterface $profileHistoryManagement;

    /**
     * @var StatusPredictionInterface
     */
    private StatusPredictionInterface $statusPrediction;

    /**
     * @var QueueProcessor\ProcessorInterface[]
     */
    private array $queues;

    /**
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param HistoryManagementInterface $profileHistoryManagement
     * @param StatusPredictionInterface $statusPrediction
     * @param array $queues
     * @param array $data
     */
    public function __construct(
        MessageStorageInterfaceFactory $messageStorageFactory,
        HistoryManagementInterface $profileHistoryManagement,
        StatusPredictionInterface $statusPrediction,
        array $queues = [],
        array $data = []
    ) {
        $this->profileHistoryManagement = $profileHistoryManagement;
        $this->statusPrediction = $statusPrediction;
        $this->profileEntity = $data['profile_entity'] ?? null;
        $this->initQueues($queues);
        parent::__construct($messageStorageFactory);
    }

    /**
     * @inheritDoc
     */
    public function getResponseStorage(): MessageStorageInterface
    {
        return $this->responseStorage;
    }

    /**
     * @inheritDoc
     */
    public function getProfileEntity()
    {
        if (!$this->profileEntity) {
            throw new LocalizedException(__('Profile entity is not set.'));
        }

        return $this->profileEntity;
    }

    /**
     * @inheritDoc
     */
    public function setProfileEntity($profileEntity)
    {
        return $this->profileEntity = $profileEntity;
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        $this->initialize();

        foreach ($this->queues as $entity => $queue) {
            try {
                $queue->execute();
            } catch (\Exception $e) {
                $this->getResponseStorage()->addData(
                    __('Queue "%1" has been processed with errors: %2', $entity, $e->getMessage()),
                    $entity,
                    Status::ERROR
                );
            }
        }

        $this->finalize();
    }

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->getResponseStorage()->resetData();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function finalize()
    {
        if (!$response = $this->getResponseStorage()->getData()) {
            return $this;
        }

        $this->profileHistoryManagement->create(
            $this->getProfileEntity()->getProfile()->getEntityId(),
            $this->getProfileEntity()->getTypeId() . '_' . self::ENTITY_CODE,
            $this->statusPrediction->execute($response, Status::COMPLETE),
            $response
        );

        return $this;
    }

    /**
     * @param QueueProcessor\ProcessorInterface[] $queues
     */
    private function initQueues(array $queues)
    {
        $this->queues = $queues;
        foreach ($this->queues as $queue) {
            $queue->init($this);
        }
    }
}
