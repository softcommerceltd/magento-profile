<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SoftCommerce\Profile\Model\ResourceModel\Profile;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SoftCommerce\Profile\Model\Profile;
use SoftCommerce\Profile\Model\ResourceModel;

/**
 * @inheritDoc
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'softcommerce_profile_entity_collection';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(Profile::class, ResourceModel\Profile::class);
    }
}
