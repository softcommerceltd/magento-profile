<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Controller\Adminhtml\Profile;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Controller\Adminhtml\Profile as ProfileController;

/**
 * @inheritDoc
 */
class Edit extends ProfileController implements HttpGetActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $profile = $this->initCurrentProfile();
        if ($id = $this->getRequest()->getParam('id')) {
            $profileUrl = $this->typeInstanceOptions->getRouter($profile);
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath(
                "$profileUrl/edit",
                [
                    ProfileInterface::ID => $id,
                    ProfileInterface::TYPE_ID => $profile->getTypeId()
                ]
            );
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage
            ->setActiveMenu('SoftCommerce_Profile::profile')
            ->addBreadcrumb(__('SoftCommerce'), __('Profile'))
            ->addBreadcrumb(__('New Profile'), __('New Profile'));

        $resultPage->getConfig()->getTitle()->prepend(__('New Profile'));

        return $resultPage;
    }
}
