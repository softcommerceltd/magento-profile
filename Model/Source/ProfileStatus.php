<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @inheritDoc
 */
class ProfileStatus implements OptionSourceInterface
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $availableOptions = [self::STATUS_ENABLED => __('Active'), self::STATUS_DISABLED => __('Inactive')];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
