<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Model\GetProfileDataInterface;
use SoftCommerce\ProfileSchedule\Model\GetScheduleDataInterface;

/**
 * @inheritDoc
 */
class ProfileEntity implements OptionSourceInterface
{
    /**
     * @var GetScheduleDataInterface
     */
    private $getProfileData;

    /**
     * @var array
     */
    private $options;

    /**
     * @param GetProfileDataInterface $getProfileData
     */
    public function __construct(GetProfileDataInterface $getProfileData)
    {
        $this->getProfileData = $getProfileData;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $this->options = [];
            foreach ($this->getProfileData->execute() as $item) {
                $this->options[] = [
                    'value' => $item[ProfileInterface::ENTITY_ID],
                    'label' => $item[ProfileInterface::NAME] ?? $item[ProfileInterface::ENTITY_ID],
                ];
            }
        }

        return $this->options;
    }
}
