<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

use Magento\Store\Api\Data\StoreInterface;
use SoftCommerce\Profile\Api\Data\ProfileInterface;

/**
 * Interface RegistryLocatorInterface
 */
interface RegistryLocatorInterface
{
    const CURRENT_PROFILE = 'softcommerce_profile';

    /**
     * @return ProfileInterface|Profile
     */
    public function getProfile();

    /**
     * @return StoreInterface
     */
    public function getStore();
}
