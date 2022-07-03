<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\System\Message;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use SoftCommerce\Profile\Profile\EntityFactoryInterface;
use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;
use SoftCommerce\ProfileConfig\Model\ResourceModel\Config;

/**
 * @inheritDoc
 */
class InitialSetupProfile implements MessageInterface
{
    const XML_PATH_AUTH_APP_URL = 'softcommerce_profile/auth/app_url';
    const XML_PATH_AUTH_APP_USERNAME = 'softcommerce_profile/auth/app_username';
    const XML_PATH_AUTH_APP_PASSWORD = 'softcommerce_profile/auth/app_password';

    /**
     * @var array
     */
    private $configs;

    /**
     * @var ScopeConfigInterface
     */
    protected $configResource;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Config $config,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder
    ) {
        $this->configResource = $config;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return md5('plenty_core' . $this->getLink());
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isDisplayed()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_AUTH_APP_URL, ScopeInterface::SCOPE_STORE)
            && $this->scopeConfig->getValue(self::XML_PATH_AUTH_APP_USERNAME, ScopeInterface::SCOPE_STORE)
            && $this->scopeConfig->getValue(self::XML_PATH_AUTH_APP_PASSWORD, ScopeInterface::SCOPE_STORE)
            && !empty($this->getAdvisedConfigs());
    }

    /**
     * @return Phrase|string
     * @throws LocalizedException
     */
    public function getText()
    {
        return __(
            'The following <a href="%1">FreeAgent</a> profiles may need to be setup: %2',
            $this->getLink(),
            ucwords(str_replace('_', ' ', implode(', ', $this->getAdvisedConfigs())))
        );
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->urlBuilder->getUrl('softcommerce/profile');
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        return MessageInterface::SEVERITY_NOTICE;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function getConfigs()
    {
        if (null === $this->configs) {
            $this->configs = [];
            $configs = $this->configResource
                ->getSearchConfigByPathAlike($this->getProfileTypes(), ConfigInterface::PATH);
            foreach ($configs as $config) {
                $result = current(explode('/', $config));
                $this->configs[$result] = str_replace('_', ' ', $result);
            }
        }

        return $this->configs;
    }

    /**
     * @return array
     */
    private function getProfileTypes()
    {
        return [
            EntityFactoryInterface::TYPE_CATEGORY_EXPORT,
            EntityFactoryInterface::TYPE_CATEGORY_IMPORT,
            EntityFactoryInterface::TYPE_PRODUCT_EXPORT,
            EntityFactoryInterface::TYPE_PRODUCT_IMPORT,
            EntityFactoryInterface::TYPE_ORDER_EXPORT,
            EntityFactoryInterface::TYPE_ORDER_IMPORT,
            EntityFactoryInterface::TYPE_STOCK_IMPORT
        ];
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function getAdvisedConfigs()
    {
        return array_flip(array_diff_key(array_flip($this->getProfileTypes()), $this->getConfigs()));
    }
}
