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
use SoftCommerce\Profile\Model\Config\ConfigScopeInterface;

/**
 * Class AbstractModifier
 */
class AbstractModifier
{
    public const FORM_NAME = 'softcommerce_profile_form';

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
        protected readonly ArrayManager $arrayManager,
        protected readonly RequestInterface $request,
        protected readonly RegistryLocatorInterface $registryLocator,
        protected readonly MetadataPoolInterface $metadataPool
    ) {
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
