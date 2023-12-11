<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Core\Framework\MessageStorageInterface;

/**
 * Interface QueueProcessorInterface used to process profile queue
 * run by profile scheduler over cronjob.
 * @deprecated
 */
interface QueueProcessorInterface
{
    public const ENTITY_CODE = 'queue_processor';

    /**
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function execute(): void;

    /**
     * @return $this
     */
    public function initialize();

    /**
     * @return $this
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function finalize(): static;

    /**
     * @return ProfileEntityInterface
     * @throws LocalizedException
     */
    public function getProfileEntity();

    /**
     * @return ProfileEntityInterface
     */
    public function setProfileEntity($profileEntity);

    /**
     * @return MessageStorageInterface
     */
    public function getResponseStorage(): MessageStorageInterface;
}
