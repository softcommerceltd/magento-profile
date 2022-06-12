<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\MassAction;

use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\Profile\Model\GetProfileDataByTypeIdInterface;

/**
 * @inheritDoc
 * Class Options used to create dynamic
 * massAction options to manage profile services.
 */
class Options implements \JsonSerializable
{
    /**
     * @var array
     */
    private $additionalData;

    /**
     * @var array
     */
    private $data;

    /**
     * @var GetProfileDataByTypeIdInterface
     */
    private $getProfileDataByTypeId;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string|null
     */
    private $paramName;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var string|null
     */
    private $urlPath;

    /**
     * @var string|null
     */
    private $profileTypeId;

    /**
     * @param GetProfileDataByTypeIdInterface $getProfileDataByTypeId
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        GetProfileDataByTypeIdInterface $getProfileDataByTypeId,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->getProfileDataByTypeId = $getProfileDataByTypeId;
        $this->urlBuilder = $urlBuilder;
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        if (null !== $this->options) {
            return $this->options;
        }

        $this->prepareData();

        $this->options = [];
        if (!$options = $this->getProfileDataByTypeId->execute($this->profileTypeId)) {
            return [];
        }

        foreach ($options as $option) {
            if (!$profileId = $option[ProfileInterface::ENTITY_ID] ?? null) {
                continue;
            }

            $profileName = $option[ProfileInterface::NAME] ?? null;
            $label = null !== $profileName
                ? __("$profileName [$profileId]")
                : $profileId;

            $this->options[$profileId] = [
                'type' => "{$this->paramName}_$profileId",
                'label' => $label,
                '__disableTmpl' => true
            ];

            if ($this->urlPath && $this->paramName) {
                $this->options[$profileId]['url'] = $this->urlBuilder->getUrl(
                    $this->urlPath,
                    [$this->paramName => $profileId]
                );
            }

            $this->options[$profileId] = array_merge_recursive(
                $this->options[$profileId],
                $this->additionalData
            );
        }

        $this->options = array_values($this->options);

        return $this->options;
    }

    /**
     * @return void
     */
    protected function prepareData(): void
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                case 'profileTypeId':
                    $this->profileTypeId = $value;
                    break;
                case 'confirm':
                    foreach ($value as $messageName => $message) {
                        $this->additionalData[$key][$messageName] = (string) new Phrase($message);
                    }
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
