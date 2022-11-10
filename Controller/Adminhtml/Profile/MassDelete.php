<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Model\ResourceModel;
use SoftCommerce\Profile\Model\ResourceModel\Profile\CollectionFactory;

/**
 * @inheritDoc
 */
class MassDelete extends AbstractMassAction
{
    /**
     * @var ResourceModel\Profile
     */
    private ResourceModel\Profile $resource;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param ResourceModel\Profile $resource
     * @param CollectionFactory $collectionFactory
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
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException|LocalizedException
     */
    protected function massAction(Collection $collection)
    {
        $ids = $collection->getAllIds();
        $result = $this->resource->remove(
            [
                ProfileInterface::ENTITY_ID . ' IN (?)' => $ids
            ]
        );

        if ($result > 0) {
            $this->messageManager->addSuccessMessage(
                __(
                    'Selected profiles have been deleted. Effected IDs: %1',
                    implode(', ', $ids)
                )
            );
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
