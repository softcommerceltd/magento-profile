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
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Core\Logger\LogProcessorInterface;
use SoftCommerce\Profile\Model\ServiceAbstract\ProcessorInterface;
use SoftCommerce\Profile\Model\ServiceAbstract\Service;
use SoftCommerce\ProfileConfig\Model\Config\LogConfigInterface;
use SoftCommerce\ProfileConfig\Model\Config\LogConfigInterfaceFactory;

/**
 * @inheritDoc
 */
class DataLog extends Service implements ProcessorInterface
{
    private const REQUEST = '__REQUEST__';
    private const RESPONSE = '__RESPONSE__';

    /**
     * @var LogProcessorInterface
     */
    private $logger;

    /**
     * @var LogConfigInterface
     */
    private $logConfig;

    /**
     * @var LogConfigInterfaceFactory
     */
    private $logConfigFactory;

    /**
     * @param LogConfigInterfaceFactory $logConfigFactory
     * @param LogProcessorInterface $logger
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        LogConfigInterfaceFactory $logConfigFactory,
        LogProcessorInterface $logger,
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->logConfigFactory = $logConfigFactory;
        $this->logger = $logger;
        parent::__construct($dataStorageFactory, $messageStorageFactory, $searchCriteriaBuilder, $data);
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        if ($this->logConfig()->isActiveRequestLog()
            && $request = $this->getContext()->getRequestStorage()->getData()
        ) {
            $this->logger->execute($this->getContext()->getTypeId(), [self::REQUEST => $request]);
        }

        if ($this->logConfig()->isActiveResponseLog()
            && $response = $this->getContext()->getResponseStorage()->getData()
        ) {
            $this->logger->execute($this->getContext()->getTypeId(), [self::RESPONSE => $response]);
        }
    }

    /**
     * @return LogConfigInterface
     * @throws LocalizedException
     */
    public function logConfig(): LogConfigInterface
    {
        if (null === $this->logConfig) {
            $this->logConfig = $this->logConfigFactory->create(
                [
                    'profileId' => $this->getContext()->getProfileId()
                ]
            );
        }
        return $this->logConfig;
    }
}
