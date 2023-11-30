<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model;

use Magento\Framework\DataObject\IdentityInterface;
use SoftCommerce\Core\Model\AbstractModel;
use SoftCommerce\Profile\Api\Data\ProfileInterface;

/**
 * @inheritDoc
 */
class Profile extends AbstractModel implements ProfileInterface, IdentityInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'softcommerce_profile_entity_model';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Profile::class);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return [$this->_eventPrefix . '_' . $this->getEntityId()];
    }

    /**
     * @inheritDoc
     */
    public function getEntityId(): int
    {
        return (int) $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name)
    {
        $this->setData(self::NAME, $name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTypeId(): ?string
    {
        return $this->getData(self::TYPE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTypeId(string $name)
    {
        $this->setData(self::TYPE_ID, $name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }
}
