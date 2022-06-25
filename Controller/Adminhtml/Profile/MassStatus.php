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
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Model\ResourceModel;
use SoftCommerce\ProfileSchedule\Api\Data\ScheduleInterface;
use SoftCommerce\ProfileSchedule\Model\ResourceModel\Schedule;

/**
 * @inheritDoc
 */
class MassStatus extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @var Schedule
     */
    private $resource;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param Schedule $resource
     * @param ResourceModel\Profile\CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Schedule $resource,
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
        $status = (int) $this->getRequest()->getParam('status');
        $result = [];
        foreach ($collection->getColumnValues(ProfileInterface::TYPE_ID) as $typeId) {
            $result[] = $this->resource->update(
                [ScheduleInterface::STATUS => $status],
                [ScheduleInterface::TYPE_ID . ' = ?' => $typeId]
            );
        }

        if ($result) {
            $this->messageManager->addSuccessMessage(
                __('Selected profiles have been updated with new status.')
            );
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
