<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Controller\Adminhtml\Profile;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use SoftCommerce\Profile\Controller\Adminhtml\Profile;

/**
 * @inheritDoc
 */
class Delete extends Profile
{
    /**
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->profileRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The profile with ID %1 has been deleted.', $id));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Could not retrieve profile ID from request data.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
