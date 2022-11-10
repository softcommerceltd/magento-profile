<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\QueueProcessor;

use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterface;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Profile\Model\QueueProcessorInterface;

/**
 * Class AbstractProcessor
 */
class AbstractProcessor
{
    /**
     * @var QueueProcessorInterface
     */
    protected $context;

    /**
     * @var MessageStorageInterfaceFactory
     */
    protected MessageStorageInterfaceFactory $messageStorageFactory;

    /**
     * @var MessageStorageInterface
     */
    protected MessageStorageInterface $responseStorage;

    /**
     * AbstractProcessor constructor.
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     */
    public function __construct(MessageStorageInterfaceFactory $messageStorageFactory)
    {
        $this->messageStorageFactory = $messageStorageFactory;
        $this->responseStorage = $this->messageStorageFactory->create();
    }

    /**
     * @param $context
     * @return $this
     */
    public function init($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return QueueProcessorInterface
     * @throws LocalizedException
     */
    public function getContext(): QueueProcessorInterface
    {
        if (false === ($this->context instanceof QueueProcessorInterface)) {
            throw new LocalizedException(__('Parent entity is not set.'));
        }
        return $this->context;
    }

    /**
     * @inheritDoc
     */
    protected function initialize()
    {
        $this->responseStorage->resetData();
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function finalize()
    {
        return $this;
    }

    /**
     * @param string $callBackName
     * @return string
     */
    protected function buildMethodName(string $callBackName): string
    {
        return lcfirst(str_replace('_', '', ucwords($callBackName, '_')));
    }
}
