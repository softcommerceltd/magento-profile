<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ServiceAbstract;

use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Core\Framework\DataStorageInterface;
use SoftCommerce\Core\Framework\MessageStorageInterface;

/**
 * Interface ServiceInterface
 * used to manage profile services.
 */
interface ServiceInterface
{
    public const SERVICE_ID = 'processor';

    /**
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void;

    /**
     * @return DataStorageInterface
     */
    public function getDataStorage(): DataStorageInterface;

    /**
     * @return DataStorageInterface
     */
    public function getRequestStorage(): DataStorageInterface;

    /**
     * @return DataStorageInterface
     */
    public function getResponseStorage(): DataStorageInterface;

    /**
     * @return MessageStorageInterface
     */
    public function getMessageStorage(): MessageStorageInterface;

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getProfileId(): int;

    /**
     * @param ServiceInterface $context
     * @return $this
     */
    public function init($context);
}
