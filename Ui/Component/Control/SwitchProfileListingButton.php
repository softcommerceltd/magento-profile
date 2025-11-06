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

/**
 * @inheritDoc
 */
class SwitchProfileListingButton implements ButtonProviderInterface
{
    private const EXPORT = 'export';
    private const IMPORT = 'import';

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $baseUrl = "{$this->urlBuilder->getBaseUrl()}admin";
        $key = $this->request->getParam('key');
        $currentUrl = $this->urlBuilder->getCurrentUrl();
        $currentUrl = str_replace([$baseUrl, 'key', $key, 'index'], '', $currentUrl);
        $urlParts = explode('/', $currentUrl);
        $urlParts = array_filter($urlParts);
        $profileEntity = (string) current($urlParts);

        if (!$typeId = $this->getTypeId((string) next($urlParts))) {
            return [];
        }

        $entityName = explode('_', $profileEntity);
        $entityName = end($entityName);
        $label = $entityName . ' ' . key($typeId);
        $typeId = current($typeId);

        return [
            'label' => __('Switch To %1', ucwords($label)),
            'on_click' => sprintf(
                "location.href = '%s';",
                $this->urlBuilder->getUrl("$profileEntity/$typeId/index")
            ),
            'class' => 'secondary',
            'sort_order' => 10
        ];
    }

    /**
     * @param string $typeId
     * @return array
     */
    private function getTypeId(string $typeId): array
    {
        switch ($typeId) {
            case self::EXPORT:
                return [self::EXPORT => self::EXPORT];
            case self::IMPORT:
                return [self::IMPORT => self::IMPORT];
        }

        $result = [];
        if (strpos($typeId, self::EXPORT) !== false) {
            $result[self::IMPORT] = str_replace(self::EXPORT, self::IMPORT, $typeId);
        } elseif (strpos($typeId, self::IMPORT) !== false) {
            $result[self::EXPORT] = str_replace(self::IMPORT, self::EXPORT, $typeId);
        }

        return $result;
    }
}
