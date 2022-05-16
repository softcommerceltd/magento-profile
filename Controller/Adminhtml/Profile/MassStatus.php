<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use SoftCommerce\Profile\Model\ResourceModel;

/**
 * @inheritDoc
 */
class MassStatus extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @var ResourceModel\Profile
     */
    private $resource;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param ResourceModel\Profile $resource
     * @param ResourceModel\Profile\CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ResourceModel\Profile $resource,
        ResourceModel\Profile\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        parent::__construct($context, $filter, $collectionFactory);
    }

    /**
     * @param Collection $collection
     * @return Redirect
     * @throws LocalizedException
     */
    protected function massAction(Collection $collection)
    {
        $ids = $collection->getAllIds();
        $status = (int) $this->getRequest()->getParam('status');
        $this->resource->updateStatus($status, $ids);

        $this->messageManager->addSuccessMessage(
            __('Selected profiles have been updated with new status.')
        );

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
