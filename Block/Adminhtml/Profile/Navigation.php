<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Block\Adminhtml\Profile;

use Magento\Backend\Block\Template;
use Magento\Framework\Phrase;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Model\TypeInstanceOptionsInterface;

/**
 * @inheritDoc
 */
class Navigation extends Template
{
    /**
     * @var string
     */
    protected $_template = 'SoftCommerce_Profile::profile/navigation.phtml';

    /**
     * @param TypeInstanceOptionsInterface $typeInstanceOptions
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        protected readonly TypeInstanceOptionsInterface $typeInstanceOptions,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
    }

    /**
     * @return array
     */
    public function getMenuItems(): array
    {
        $result = [];
        $profileId = $this->getRequest()->getParam(ProfileInterface::ID);
        $typeId = $this->getRequest()->getParam(ProfileInterface::TYPE_ID);
        if ($typeId && $rooter = $this->typeInstanceOptions->getQueueRouterByTypeId($typeId)) {
            $name = explode('_', $typeId);
            array_shift($name);
            $name = implode(' ', $name);
            $result[$typeId] = [
                'index' => $typeId,
                'name' => ucwords($name) . ' Queue',
                'url' => $this->_urlBuilder->getUrl(
                    $rooter,
                    [
                        ProfileInterface::PROFILE_ID => $profileId,
                        ProfileInterface::TYPE_ID => $typeId
                    ]
                )
            ];
        }

        $currentUrlPath = $this->getCurrentUrlPath();
        foreach ($this->getData('menu_items') ?: [] as $itemName => $itemLink) {
            if ($currentUrlPath !== $itemLink) {
                $result[$itemName] = [
                    'index' => $itemLink,
                    'name' => ucwords(str_replace('_', ' ', $itemName)),
                    'url' => $this->_urlBuilder->getUrl(
                        $itemLink,
                        [
                            ProfileInterface::PROFILE_ID => $profileId,
                            ProfileInterface::TYPE_ID => $typeId
                        ]
                    )
                ];
            }
        }

        return $result;
    }

    /**
     * @return Phrase
     */
    public function getTitle()
    {
        return __('Profile Links');
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getPaddingClass(): string
    {
        $parentBlock = $this->getParentBlock();
        return $parentBlock && count($parentBlock->getChildNames()) > 1
            ? 'pl-3'
            : '';
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        if ($this->isVisible()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return string
     */
    private function getCurrentUrlPath(): string
    {
        $baseUrl = "{$this->_urlBuilder->getBaseUrl()}admin";
        $key = $this->getRequest()->getParam('key', '');
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        $currentUrl = str_replace([$baseUrl, 'key', $key, 'index'], '', $currentUrl);
        $urlParts = explode('/', $currentUrl);
        $urlParts = array_filter($urlParts);
        return implode('/', $urlParts);
    }
}
