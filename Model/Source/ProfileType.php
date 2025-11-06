<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use SoftCommerce\Profile\Model\TypeInstanceOptionsInterface;

/**
 * @inheritDoc
 */
class ProfileType implements OptionSourceInterface
{
    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @param TypeInstanceOptionsInterface $typeInstanceOptions
     */
    public function __construct(
        private readonly TypeInstanceOptionsInterface $typeInstanceOptions
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): ?array
    {
        if (null === $this->options) {
            $this->options = [];
            foreach ($this->typeInstanceOptions->getOptionArray() as $index => $value) {
                $this->options[] = [
                    'value' => $index,
                    'label' => $value
                ];
            }
        }

        return $this->options;
    }
}
