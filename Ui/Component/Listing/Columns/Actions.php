<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\Component\Listing\Columns;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Model\GetProfileDataByTypeIdInterface;

/**
 * @inheritDoc
 * Class Actions used to provide dynamic
 * row action options to manage profile services.
 */
class Actions extends Column
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var GetProfileDataByTypeIdInterface
     */
    protected $getProfileDataByTypeId;

    /**
     * @var array
     */
    protected $profiles;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Escaper $escaper
     * @param GetProfileDataByTypeIdInterface $getProfileDataByTypeId
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Escaper $escaper,
        GetProfileDataByTypeIdInterface $getProfileDataByTypeId,
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->getProfileDataByTypeId = $getProfileDataByTypeId;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $profileTypeId = $this->getData('config/profileTypeId');
        if (!$profileTypeId || !isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $identifierField = $this->getData('config/identifierField') ?: 'entity_id';
        $urlParamName = $this->getData('config/urlEntityParamName') ?: 'id';
        $actionList = $this->getData('actionList');
        foreach ($dataSource['data']['items'] as & $item) {
            if (!isset($item[$identifierField])) {
                continue;
            }

            foreach ($actionList as $actionTypeId => $actionItem) {
                $urlPath = $actionItem['urlPath'] ?? '#';
                $label = $actionItem['label'] ?? __('Manage');
                $confirmTitle = $actionItem['confirmTitle'] ?? __('Confirm current action');
                $confirmMessage = $actionItem['confirmMessage'] ?? __('Proceed with current action?');

                $item[$this->getData('name')][$actionTypeId] = [
                    'href' => $this->urlBuilder->getUrl(
                        $urlPath,
                        [
                            $urlParamName => $item[$identifierField],
                            ProfileInterface::TYPE_ID => $profileTypeId
                        ]
                    ),
                    'label' => $label,
                    'confirm' => [
                        'title' => __($this->escaper->escapeHtml($confirmTitle)),
                        'message' => __($this->escaper->escapeHtml($confirmMessage))
                    ],
                    'post' => true
                ];
            }
        }

        return $dataSource;
    }
}
