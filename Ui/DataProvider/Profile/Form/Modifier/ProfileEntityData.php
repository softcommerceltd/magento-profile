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
    const DATA_SOURCE = 'profile_entity';

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $data += [
            $this->registryLocator->getProfile()->getEntityId() => [
                self::DATA_SOURCE => $this->registryLocator->getProfile()->toArray()
            ]
        ];

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
