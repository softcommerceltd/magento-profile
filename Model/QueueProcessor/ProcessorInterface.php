<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\QueueProcessor;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Profile\Model\QueueProcessorInterface;

/**
 * Interface ProcessorInterface used to process profile queue.
 */
interface ProcessorInterface
{
    /**
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function execute(): void;

    /**
     * @param $context
     * @return $this
     */
    public function init($context);

    /**
     * @return QueueProcessorInterface
     * @throws LocalizedException
     */
    public function getContext(): QueueProcessorInterface;
}
