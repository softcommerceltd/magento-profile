<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Framework\Controller\ResultInterface;

/**
 * @inheritDoc
 */
class Index extends Action
{
    /**
     * @inheritDoc
     */
    const ADMIN_RESOURCE = 'SoftCommerce_Profile::profile';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * @return Forward|ResponseInterface|ResultInterface|PageFactory
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('SoftCommerce_Profile::profile');
        $resultPage->getConfig()->getTitle()->prepend(__('Profiles'));
        $resultPage->addBreadcrumb(__('Profiles'), __('Profiles'));
        $resultPage->addBreadcrumb(__('Manage Profiles'), __('Manage Profiles'));

        return $resultPage;
    }
}
