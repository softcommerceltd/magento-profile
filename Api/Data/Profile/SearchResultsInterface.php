<?php
/**
 * Copyright © Byte8 Ltd (formerly Soft Commerce). All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SoftCommerce\Profile\Api\Data\Profile;

use Magento\Framework\Api;

/**
 * Interface SearchResultsInterface
 */
interface SearchResultsInterface extends Api\SearchResultsInterface
{
    /**
     * @return Api\ExtensibleDataInterface[]
     */
    public function getItems();

    /**
     * @param array $items
     * @return Api\SearchResultsInterface
     */
    public function setItems(array $items);
}
