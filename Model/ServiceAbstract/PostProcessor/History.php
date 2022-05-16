<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ServiceAbstract\PostProcessor;

use Magento\Framework\Api\SearchCriteriaBuilder;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorage\StatusPredictionInterface;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Profile\Model\ServiceAbstract\ProcessorInterface;
use SoftCommerce\Profile\Model\ServiceAbstract\Service;
use SoftCommerce\ProfileHistory\Api\HistoryManagementInterface;

/**
 * @inheritDoc
 */
class History extends Service implements ProcessorInterface
{
    /**
     * @var HistoryManagementInterface
     */
    private $historyManagement;

    /**
     * @var StatusPredictionInterface
     */
    private $statusPrediction;

    /**
     * @param HistoryManagementInterface $historyManagement
     * @param StatusPredictionInterface $statusPrediction
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        HistoryManagementInterface $historyManagement,
        StatusPredictionInterface $statusPrediction,
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->historyManagement = $historyManagement;
        $this->statusPrediction = $statusPrediction;
        parent::__construct($dataStorageFactory, $messageStorageFactory, $searchCriteriaBuilder, $data);
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        if (!$response = $this->getContext()->getMessageStorage()->getData()) {
            return;
        }

        $this->historyManagement->create(
            $this->getContext()->getProfileId(),
            $this->getContext()->getTypeId(),
            $this->statusPrediction->execute($response),
            $response
        );
    }
}
