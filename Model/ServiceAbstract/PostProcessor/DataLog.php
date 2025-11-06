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
use SoftCommerce\Core\Framework\MessageCollectorInterfaceFactory;
use SoftCommerce\Core\Logger\LogProcessorInterface;
use SoftCommerce\Profile\Model\ServiceAbstract\ProcessorInterface;
use SoftCommerce\Profile\Model\ServiceAbstract\Service;
use SoftCommerce\Profile\Model\Config\Type\LogConfigInterface;
use SoftCommerce\Profile\Model\Config\Type\LogConfigInterfaceFactory;

/**
 * @inheritDoc
 */
class DataLog extends Service implements ProcessorInterface
{
    private const REQUEST = '__REQUEST__';
    private const RESPONSE = '__RESPONSE__';

    /**
     * @var LogConfigInterface|null
     */
    private ?LogConfigInterface $logConfig = null;

    /**
     * @param LogConfigInterfaceFactory $logConfigFactory
     * @param LogProcessorInterface $logger
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageCollectorInterfaceFactory $messageCollectorFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        private readonly LogConfigInterfaceFactory $logConfigFactory,
        private readonly LogProcessorInterface $logger,
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageCollectorInterfaceFactory $messageCollectorFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct(
            $dataStorageFactory,
            $messageCollectorFactory,
            $searchCriteriaBuilder,
            $data
        );
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
