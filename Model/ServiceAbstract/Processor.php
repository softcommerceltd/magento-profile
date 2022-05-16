<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ServiceAbstract;

use Magento\Framework\Api\SearchCriteriaBuilder;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;

/**
 * @inheritDoc
 */
class Processor extends Service implements ServiceInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     * @param array $processors
     */
    public function __construct(
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = [],
        array $processors = []
    ) {
        $this->processors = $this->initServices($processors);
        parent::__construct($dataStorageFactory, $messageStorageFactory, $searchCriteriaBuilder, $data);
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        $this->initialize();

        foreach ($this->processors as $entity => $processor) {
            $processor->execute();
        }

        $this->finalize();
    }

    /**
     * @return Processor
     */
    public function initialize()
    {
        $this->initTypeInstances($this->context, $this->processors);
        return parent::initialize();
    }
}
