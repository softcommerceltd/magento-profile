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
class Behaviour implements OptionSourceInterface
{
    const APPEND = 'append';
    const REPLACE = 'replace';
    const DELETE = 'delete';

    /**
     * Options array
     *
     * @var array
     */
    private $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['value' => self::APPEND, 'label' => __('Add/Update')],
                ['value' => self::REPLACE, 'label' => __('Replace')],
                ['value' => self::DELETE, 'label' => __('Delete')],
            ];
        }

        return $this->options;
    }
}
