<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Block\Adminhtml\Wizard\Steps;

use Magento\Framework\Phrase;
use Magento\Ui\Block\Component\StepsWizard\StepAbstract;

/**
 * @inheritDoc
 */
abstract class AbstractStep extends StepAbstract
{
    /**
     * @return string
     */
    public function getForm(): string
    {
        return $this->getData('config/form');
    }

    /**
     * @return Phrase
     */
    public function getClientIdErrorMsg(): Phrase
    {
        return __('Client ID has not been set. Please select a client from previous step.');
    }

    /**
     * @return Phrase
     */
    public function getUrlIsRequiredMsg(): Phrase
    {
        return __('The URL is required.');
    }

    /**
     * @return string
     */
    public function getButtonActionsJson(): string
    {
        return json_encode(
            [
                [
                    'targetName' => $this->getData('config/form'). '.' . $this->getComponentName(),
                    'actionName' => 'action',
                    'actionUrl' => $this->getUrl('plenty/client/connection')
                ]
            ]
        );
    }
}
