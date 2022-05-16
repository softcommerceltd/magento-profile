<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ResourceModel;

use SoftCommerce\Core\Model\ResourceModel\AbstractResource;
use SoftCommerce\Profile\Api\Data\ProfileInterface;

/**
 * @inheritDoc
 */
class Profile extends AbstractResource
{
    /**
     * @inheritDoc
     */
    protected $_useIsObjectNew = true;

    /**
     * @var string
     */
    protected $_eventPrefix = 'softcommerce_profile_entity_resource_model';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ProfileInterface::DB_TABLE_NAME, ProfileInterface::ENTITY_ID);
    }
}
