<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\DataProvider\Profile\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * @inheritDoc
 */
class ProfileEntityData extends AbstractModifier implements ModifierInterface
{
    public const DATA_SOURCE = 'profile_entity';

    /**
     * @inheritDoc
     */
    public function modifyData(array $data): array
    {
        $data += [
            $this->registryLocator->getProfile()->getEntityId() => [
                self::DATA_SOURCE => $this->registryLocator->getProfile()->toArray()
            ]
        ];

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
