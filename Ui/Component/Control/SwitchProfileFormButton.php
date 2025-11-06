<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\Component\Control;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use SoftCommerce\Core\Ui\Component\Control\FontAwesomeButton;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Model\GetProfileDataByTypeIdInterface;
use SoftCommerce\Profile\Model\TypeInstanceOptionsInterface;

/**
 * @inheritDoc
 */
class SwitchProfileFormButton implements ButtonProviderInterface
{
    private const EXPORT = 'export';
    private const IMPORT = 'import';

    /**
     * @param GetProfileDataByTypeIdInterface $getProfileDataByTypeId
     * @param RequestInterface $request
     * @param TypeInstanceOptionsInterface $typeInstanceOptions
     * @param UrlInterface $urlBuilder
     * @param string|null $fontName
     * @param string|null $label
     */
    public function __construct(
        protected readonly GetProfileDataByTypeIdInterface $getProfileDataByTypeId,
        protected readonly RequestInterface $request,
        protected readonly TypeInstanceOptionsInterface $typeInstanceOptions,
        protected readonly UrlInterface $urlBuilder,
        protected ?string $fontName = null,
        protected ?string $label = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        if (!$typeId = $this->getTypeId()) {
            return [];
        }

        $profileUrl = $this->typeInstanceOptions->getRouterByTypeId($typeId);
        if (!$profileId = $this->getProfileDataByTypeId->execute($typeId, ProfileInterface::ENTITY_ID)) {
            return [];
        }

        $data = [
            'on_click' => sprintf(
                "location.href = '%s';",
                $this->urlBuilder->getUrl(
                    "$profileUrl/edit",
                    [
                        ProfileInterface::ID => $profileId,
                        ProfileInterface::TYPE_ID => $typeId
                    ]
                )
            ),
            'class_name' => FontAwesomeButton::class,
            FontAwesomeButton::FONT_NAME => $this->fontName ?: 'fa-solid fa-arrows-rotate',
            'class' => 'secondary',
            'sort_order' => 10
        ];

        if ($this->label) {
            $data['label'] = $this->label;
        }

        return $data;
    }

    /**
     * @return string|null
     */
    private function getTypeId(): ?string
    {
        $typeId = explode('_', $this->request->getParam(ProfileInterface::TYPE_ID, ''));
        $index = array_pop($typeId);

        $result = null;
        switch ($index) {
            case self::EXPORT:
                $result = self::IMPORT;
                break;
            case self::IMPORT:
                $result = self::EXPORT;
                break;
        }

        if (null !== $result) {
            $typeId[] = $result;
            $result = implode('_', $typeId);
        }

        return $result;
    }
}
