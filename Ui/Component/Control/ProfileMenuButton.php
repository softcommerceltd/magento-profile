<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\Component\Control;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

/**
 * @inheritDoc
 */
class ProfileMenuButton implements ButtonProviderInterface
{
    /**
     * @var string|null
     */
    protected $aclResource;

    /**
     * @var array
     */
    private $actionData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var string|null
     */
    protected $label;

    /**
     * @var int|null
     */
    protected $sortOrder;

    /**
     * @param RequestInterface $request
     * @param array $actionData
     * @param string|null $aclResource
     * @param string|null $label
     * @param int|null $sortOrder
     */
    public function __construct(
        RequestInterface $request,
        array $actionData,
        ?string $aclResource = null,
        ?string $label = null,
        ?int $sortOrder = null
    ) {
        $this->request = $request;
        $this->actionData = $actionData;
        $this->aclResource = $aclResource;
        $this->label = $label;
        $this->sortOrder = $sortOrder;
    }

    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        $data = [
            'label' => __($this->label ?: 'Profile Menu'),
            'class' => 'secondary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            []
                        ]
                    ]
                ]
            ],
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getOptions(),
            'sort_order' => $this->sortOrder ?: 30,
        ];

        return $this->processExtraParameters($data);
    }

    /**
     * @return array
     */
    private function getOptions(): array
    {
        if (!$this->canShowOptions()) {
            return [];
        }

        $result = [];
        $i = 0;
        foreach ($this->actionData ?: [] as $typeId => $itemData) {
            $targetName = $itemData['targetName'] ?? null;
            if (!$targetName || !$actionName = $itemData['actionName'] ?? null) {
                continue;
            }

            $result[$typeId] = [
                'label' => __($itemData['label'] ?? $actionName),
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => $targetName,
                                    'actionName' => $actionName,
                                    'params' => [
                                        true
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'sort_order' => isset($itemData['sort_order']) ? (int) $itemData['sort_order'] : $i,
            ];
            $i++;
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getExtraParameters(): array
    {
        $extraParams = [];
        if ($this->isModal()) {
            $extraParams['isModal'] = 1;
        }
        if ($this->isPopup()) {
            $extraParams['popup'] = 1;
        }
        return $extraParams;
    }

    /**
     * @param array $data
     * @return array
     */
    private function processExtraParameters(array $data): array
    {
        if (!empty($data)) {
            if (null !== $this->aclResource) {
                $data['aclResource'] = $this->aclResource;
            }
            if (null !== $this->sortOrder) {
                $data['sort_order'] = $this->sortOrder;
            }
        }

        return $data;
    }

    /**
     * @return bool
     */
    private function canShowOptions(): bool
    {
        return !$this->isModal() && !$this->isPopup();
    }

    /**
     * @return bool
     */
    private function isModal(): bool
    {
        return (bool) $this->request->getParam('isModal');
    }

    /**
     * @return bool
     */
    private function isPopup(): bool
    {
        return (bool) $this->request->getParam('popup');
    }
}
