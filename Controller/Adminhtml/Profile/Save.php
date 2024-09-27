<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Api\ProfileRepositoryInterface;
use SoftCommerce\Profile\Controller\Adminhtml\Profile as ProfileController;
use SoftCommerce\Profile\Model\Profile as ProfileModel;
use SoftCommerce\Profile\Model\ProfileFactory;
use SoftCommerce\Profile\Model\TypeInstanceOptionsInterface;
use SoftCommerce\Profile\Ui\DataProvider\Profile\Config\Form\ConfigDataScopeStorageInterface;
use SoftCommerce\Profile\Ui\DataProvider\Profile\Form\Modifier\ProfileConfigData;
use SoftCommerce\Profile\Ui\DataProvider\Profile\Form\Modifier\ProfileEntityData;
use SoftCommerce\ProfileConfig\Model\ConfigScopeInterface;

/**
 * @inheritDoc
 */
class Save extends ProfileController
{
    /**
     * @var ConfigDataScopeStorageInterface
     */
    private ConfigDataScopeStorageInterface $configDataScopeStorage;

    /**
     * @var DataPersistorInterface
     */
    private DataPersistorInterface $dataPersistor;

    /**
     * @param ConfigDataScopeStorageInterface $configDataScopeStorage
     * @param DataPersistorInterface $dataPersistor
     * @param ConfigScopeInterface $configScope
     * @param Registry $coreRegistry
     * @param ProfileFactory $profileFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param LayoutFactory $resultLayoutFactory
     * @param TypeInstanceOptionsInterface $typeInstanceOptions
     * @param Action\Context $context
     */
    public function __construct(
        ConfigDataScopeStorageInterface $configDataScopeStorage,
        DataPersistorInterface $dataPersistor,
        ConfigScopeInterface $configScope,
        Registry $coreRegistry,
        ProfileFactory $profileFactory,
        ProfileRepositoryInterface $profileRepository,
        LayoutFactory $resultLayoutFactory,
        TypeInstanceOptionsInterface $typeInstanceOptions,
        Action\Context $context
    ) {
        $this->configDataScopeStorage = $configDataScopeStorage;
        $this->dataPersistor = $dataPersistor;
        parent::__construct(
            $configScope,
            $coreRegistry,
            $profileFactory,
            $profileRepository,
            $resultLayoutFactory,
            $typeInstanceOptions,
            $context
        );
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $this->saveProfile();
            $this->saveProfileConfig();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath(self::PROFILE_BASE_URL, ['_current' => true]);
        }

        if ($this->getRequest()->getParam('back') && $id = $this->getProfile()->getEntityId()) {
            $profileUrl = $this->typeInstanceOptions->getRouter($this->getProfile());
            $params = ['id' => $id, 'type_id' => $this->getProfile()->getTypeId()];
            if ($websiteScopeId = $this->getRequest()->getParam('website')) {
                $params['website'] = $websiteScopeId;
            } elseif ($storeScopeId = $this->getRequest()->getParam('store')) {
                $params['store'] = $storeScopeId;
            }
            return $resultRedirect->setPath("{$profileUrl}/edit", $params);
        }

        return $resultRedirect->setPath(self::PROFILE_BASE_URL);
    }

    /**
     * @return ProfileInterface
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    private function saveProfile(): ProfileInterface
    {
        if (!$profileData = $this->extractProfileData()) {
            throw new LocalizedException(__('Could not retrieve profile data.'));
        }

        /** @var ProfileInterface|ProfileModel $profile */
        $profile = $this->initCurrentProfile();
        if ($this->getRequest()->getParam('id')) {
            $profile->addData($profileData);
        } else {
            $profile->setData($profileData);
        }

        try {
            $this->currentProfile = $this->profileRepository->save($profile);
            $this->messageManager->addSuccessMessage(__('Profile has been saved.'));
            $this->dataPersistor->clear('profile');
        } catch (\Exception $e) {
            $this->dataPersistor->set('profile', $profileData);
            throw $e;
        }

        return $this->currentProfile;
    }

    /**
     * @return void
     */
    private function saveProfileConfig(): void
    {
        if (!$this->getRequest()->getParam(ProfileConfigData::DATA_SOURCE)) {
            return;
        }

        try {
            $this->_eventManager->dispatch(
                'softcommerce_profile_config_save_before',
                ['request' => $this->getRequest()]
            );

            $configData = $this->configDataScopeStorage->saveFormData($this->getRequest()->getParams());
            $this->messageManager->addSuccessMessage(__('Profile configuration has been saved.'));

            $this->_eventManager->dispatch(
                'softcommerce_profile_config_save_after',
                ['config_data' => $configData, 'request' => $this->getRequest()]
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving profile configuration. %1', $e->getMessage())
            );
        }
    }

    /**
     * @return array
     */
    private function extractProfileData(): array
    {
        $result = [];
        if (!$request = $this->getRequest()->getParam(ProfileEntityData::DATA_SOURCE)) {
            return $result;
        }

        if (isset($request[ProfileInterface::ENTITY_ID])) {
            $result[ProfileInterface::ENTITY_ID] = $request[ProfileInterface::ENTITY_ID];
        }

        if (isset($request[ProfileInterface::NAME])) {
            $result[ProfileInterface::NAME] = $request[ProfileInterface::NAME];
        }

        if (isset($request[ProfileInterface::STATUS])) {
            $result[ProfileInterface::STATUS] = $request[ProfileInterface::STATUS];
        }

        if ($this->getRequest()->getParam('id')) {
            return $result;
        }

        if (isset($request[ProfileInterface::TYPE_ID])) {
            $result[ProfileInterface::TYPE_ID] = $request[ProfileInterface::TYPE_ID];
        }

        return $result;
    }
}
