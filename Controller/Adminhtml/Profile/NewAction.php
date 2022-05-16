<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Controller\Adminhtml\Profile;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use SoftCommerce\Profile\Controller\Adminhtml\Profile as ProfileController;

/**
 * @inheritDoc
 */
class NewAction extends ProfileController
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
