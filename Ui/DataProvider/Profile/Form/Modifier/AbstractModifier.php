<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\DataProvider\Profile\Form\Modifier;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\ArrayManager;
use SoftCommerce\Profile\Model\RegistryLocatorInterface;
use SoftCommerce\Profile\Ui\DataProvider\Modifier\Form\MetadataPoolInterface;
use SoftCommerce\ProfileConfig\Model\ConfigScopeInterface;

/**
 * Class AbstractModifier
 */
class AbstractModifier
{
    const FORM_NAME = 'softcommerce_profile_form';

    /**
     * @var ArrayManager
     */
    protected ArrayManager $arrayManager;

    /**
     * @var RegistryLocatorInterface
     */
    protected RegistryLocatorInterface $registryLocator;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var MetadataPoolInterface
     */
    protected MetadataPoolInterface $metadataPool;

    /**
     * @var int|null
     */
    protected ?int $profileId = null;

    /**
     * @var string|null
     */
    protected ?string $typeId = null;

    /**
     * @param ArrayManager $arrayManager
     * @param RequestInterface $request
     * @param RegistryLocatorInterface $registryLocator
     * @param MetadataPoolInterface $metadataPool
     */
    public function __construct(
        ArrayManager $arrayManager,
        RequestInterface $request,
        RegistryLocatorInterface $registryLocator,
        MetadataPoolInterface $metadataPool
    ) {
        $this->arrayManager = $arrayManager;
        $this->request = $request;
        $this->registryLocator = $registryLocator;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    protected function getProfileId(): int
    {
        if (null !== $this->profileId) {
            return $this->profileId;
        }

        if (!$profileId = $this->request->getParam(ConfigScopeInterface::REQUEST_ID)) {
            throw new LocalizedException(__('Profile ID is not set.'));
        }

        $this->profileId = (int) $profileId;
        return $this->profileId;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    protected function getTypeId(): string
    {
        if (null !== $this->typeId) {
            return $this->typeId;
        }

        if (!$typeId = $this->request->getParam(ConfigScopeInterface::REQUEST_TYPE_ID)) {
            throw new LocalizedException(__('Profile type ID is not set.'));
        }

        $this->typeId = (string) $typeId;
        return $this->typeId;
    }
}
