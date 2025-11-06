<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Api\ProfileRepositoryInterface;
use SoftCommerce\Profile\Model\ProfileFactory;
use SoftCommerce\Profile\Model\RegistryLocatorInterface;
use SoftCommerce\Profile\Model\TypeInstanceOptionsInterface;
use SoftCommerce\Profile\Model\Config\ConfigScopeInterface;

/**
 * @inheritDoc
 */
abstract class Profile extends Action
{
    /**
     * @inheritDoc
     */
    public const ADMIN_RESOURCE = 'SoftCommerce_Profile::manage';

    /**
     * Profile base Url
     */
    protected const PROFILE_BASE_URL = 'softcommerce/profile';

    /**
     * Client xml config path
     */
    protected const XML_PATH_CLIENT_ID = '/client_config/client_id';

    /**
     * @var ProfileInterface|Profile
     */
    protected $currentProfile;

    /**
     * @param ConfigScopeInterface $configScope
     * @param Registry $coreRegistry
     * @param ProfileFactory $profileFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param LayoutFactory $resultLayoutFactory
     * @param TypeInstanceOptionsInterface $typeInstanceOptions
     * @param Action\Context $context
     */
    public function __construct(
        protected readonly ConfigScopeInterface $configScope,
        protected readonly Registry $coreRegistry,
        protected readonly ProfileFactory $profileFactory,
        protected readonly ProfileRepositoryInterface $profileRepository,
        protected readonly LayoutFactory $resultLayoutFactory,
        protected readonly TypeInstanceOptionsInterface $typeInstanceOptions,
        Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return ProfileInterface
     */
    protected function initCurrentProfile()
    {
        $this->currentProfile = $this->profileFactory->create();

        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->currentProfile = $this->profileRepository->getById($id);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Could not find profile with ID: %1.', $id));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $this->currentProfile;
            }
        }

        $this->coreRegistry->register(RegistryLocatorInterface::CURRENT_PROFILE, $this->currentProfile);

        return $this->currentProfile;
    }

    /**
     * @return int|null
     */
    protected function initCurrentProfileId(): ?int
    {
        if ($profileId = $this->getProfileIdParam()) {
            $this->coreRegistry->register('current_profile_id', $profileId);
        }
        return $profileId;
    }

    /**
     * @return ProfileInterface|Profile
     */
    protected function getProfile(): ProfileInterface
    {
        return $this->currentProfile;
    }

    /**
     * @return int|null
     */
    protected function getClientIdParam(): ?int
    {
        return (int) $this->getRequest()->getParam('set') ?: null;
    }

    /**
     * @param int|null $clientId
     * @return $this
     */
    protected function setClientIdParam(?int $clientId = null)
    {
        $this->getRequest()->setParam('set', $clientId);
        return $this;
    }

    /**
     * @return int|null
     */
    protected function getProfileIdParam(): ?int
    {
        return (int) $this->getRequest()->getParam('id') ?: null;
    }
}
