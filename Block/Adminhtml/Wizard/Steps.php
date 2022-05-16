<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Block\Adminhtml\Wizard;

use Magento\Backend\Block\Template;
use Magento\Ui\Block\Component\StepsWizard;

/**
 * @inheritDoc
 */
class Steps extends Template
{
    /**
     * @return string
     */
    public function getProvider(): string
    {
        return (string) $this->getData('config/provider');
    }

    /**
     * @return string
     */
    public function getModal(): string
    {
        return (string) $this->getData('config/modal');
    }

    /**
     * @return string
     */
    public function getForm(): string
    {
        return (string) $this->getData('config/form');
    }

    /**
     * @return array
     */
    public function getEventHandlerComponents(): array
    {
        return $this->getData('config/eventHandlerComponent') ?: [];
    }

    /**
     * @param array $data
     * @return string
     */
    public function getWizardBlock(array $data)
    {
        /** @var StepsWizard $wizardBlock */
        $wizardBlock = $this->getChildBlock($this->getData('config/nameStepWizard'));
        if ($wizardBlock) {
            $wizardBlock->setInitData($data);
            return $wizardBlock->toHtml();
        }
        return '';
    }
}
