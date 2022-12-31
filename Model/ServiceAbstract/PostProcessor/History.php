<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ServiceAbstract\PostProcessor;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorage\StatusPredictionInterface;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Profile\Model\ServiceAbstract\ProcessorInterface;
use SoftCommerce\Profile\Model\ServiceAbstract\Service;
use SoftCommerce\ProfileHistory\Api\HistoryManagementInterface;
use SoftCommerce\ProfileSchedule\Model\Config\ScheduleConfigInterface;
use SoftCommerce\ProfileSchedule\Model\Config\ScheduleConfigInterfaceFactory;
/**
 * @inheritDoc
 */
class History extends Service implements ProcessorInterface
{
    private const BATCH_LIMIT = 50;

    /**
     * @var HistoryManagementInterface
     */
    private HistoryManagementInterface $historyManagement;

    /**
     * @var ScheduleConfigInterface|null
     */
    private ?ScheduleConfigInterface $scheduleConfig = null;

    /**
     * @var ScheduleConfigInterfaceFactory
     */
    private ScheduleConfigInterfaceFactory $scheduleConfigFactory;

    /**
     * @var StatusPredictionInterface
     */
    private StatusPredictionInterface $statusPrediction;

    /**
     * @param HistoryManagementInterface $historyManagement
     * @param ScheduleConfigInterfaceFactory $scheduleConfigFactory
     * @param StatusPredictionInterface $statusPrediction
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        HistoryManagementInterface $historyManagement,
        ScheduleConfigInterfaceFactory $scheduleConfigFactory,
        StatusPredictionInterface $statusPrediction,
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->historyManagement = $historyManagement;
        $this->scheduleConfigFactory = $scheduleConfigFactory;
        $this->statusPrediction = $statusPrediction;
        parent::__construct($dataStorageFactory, $messageStorageFactory, $searchCriteriaBuilder, $data);
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        if (!$this->getConfig()->isActiveHistory()) {
            return;
        }

        foreach (array_chunk($this->getContext()->getMessageStorage()->getData(), self::BATCH_LIMIT) as $batch) {
            $this->historyManagement->create(
                $this->getContext()->getProfileId(),
                $this->getContext()->getTypeId(),
                $this->statusPrediction->execute($batch),
                $batch
            );
        }
    }

    /**
     * @return ScheduleConfigInterface
     * @throws LocalizedException
     */
    private function getConfig(): ScheduleConfigInterface
    {
        if (null === $this->scheduleConfig) {
            $this->scheduleConfig = $this->scheduleConfigFactory->create(
                ['profileId' => $this->getContext()->getProfileId()]
            );
        }
        return $this->scheduleConfig;
    }
}
