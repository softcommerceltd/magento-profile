<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

use Magento\Framework\Data\OptionSourceInterface;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Model\ProfileTypes\ConfigInterface;

/**
 * @inheritDoc
 */
class TypeInstanceOptions implements TypeInstanceOptionsInterface, OptionSourceInterface
{
    /**
     * @var array|null
     */
    private ?array $types = null;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getOptionArray(): array
    {
        $options = [];
        foreach ($this->getTypes() as $typeId => $type) {
            $options[$typeId] = (string) $type['label'];
        }
        return $options;
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        $result = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getTypes(?string $typeId = null): array
    {
        if (null === $this->types) {
            $this->types = $this->config->getAll();
        }

        return null !== $typeId
            ? ($this->types[$typeId] ?? [])
            : $this->types;
    }

    /**
     * @inheritDoc
     */
    public function getRouter(ProfileInterface $profile): ?string
    {
        return $this->getTypes()[$profile->getTypeId()]['router'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getRouterByTypeId(string $typeId): ?string
    {
        return $this->getTypes($typeId)['router'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getQueueRouterByTypeId(string $typeId): ?string
    {
        return $this->getTypes($typeId)['queue_router'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getCronGroupByTypeId(string $typeId): ?string
    {
        return $this->getTypes($typeId)['crontab_group'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getLabel(string $typeId): ?string
    {
        return $this->getTypes($typeId)['label'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
