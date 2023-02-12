<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ServiceAbstract;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface CanProcessChainInterface
 * used to validate the process chain.
 */
interface CanProcessChainInterface
{
    /**
     * @param Service $context
     * @param DataObject[] $subjects
     * @return bool
     * @throws LocalizedException
     */
    public function execute(Service $context, array $subjects = []): bool;
}
