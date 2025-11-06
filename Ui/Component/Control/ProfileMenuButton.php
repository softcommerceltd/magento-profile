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
use Magento\Ui\Component\Control\Container;

/**
 * @inheritDoc
 */
class ProfileMenuButton implements ButtonProviderInterface
{
    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $actionData
     * @param string|null $aclResource
     * @param string|null $label
     * @param int|null $sortOrder
     */
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly UrlInterface $urlBuilder,
        protected array $actionData,
        protected ?string $aclResource = null,
        protected ?string $label = null,
        protected ?int $sortOrder = null
    ) {}

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

        usort($this->actionData, function (array $a, array $b) {
            return $this->getSortOrder($a) <=> $this->getSortOrder($b);
        });

        $result = [];
        $i = 0;
        foreach ($this->actionData as $typeId => $itemData) {
            $targetName = $itemData['targetName'] ?? null;
            $actionName = $itemData['actionName'] ?? null;
            $url = $itemData['url'] ?? null;

            if (!$targetName && !$actionName && !$url) {
                continue;
            }

            $result[$typeId] = [
                'label' => __($itemData['label'] ?? $actionName),
                'sort_order' => $i,
            ];

            if ($actionName) {
                $result[$typeId]['data_attribute'] = [
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
                ];
            }

            if ($url) {
                $params = $itemData['params'] ?? [];
                $result[$typeId]['onclick'] = $this->getOnclickUrl($url, $itemData['confirm'] ?? null, $params);
            }

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
     * @param string $path
     * @param string|null $confirm
     * @param array $params
     * @return string
     */
    private function getOnclickUrl(string $path, ?string $confirm = null, array $params = []): string
    {
        $message = null;
        if ($confirm) {
            $message = __($confirm);
        }

        $url = $this->urlBuilder->getUrl($path, $params);

        return $message
            ? "confirmSetLocation('$message', '$url')"
            : "setLocation('$url')";
    }

    /**
     * @param array $item
     * @return int
     */
    private function getSortOrder(array $item): int
    {
        return (int) ($item['sort_order'] ?? 0);
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
