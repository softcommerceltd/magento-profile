<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\System\Message;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @inheritDoc
 */
class InitialSetupSystem implements MessageInterface
{
    const XML_PATH_AUTH_APP_URL = 'profile_config/auth/app_url';
    const XML_PATH_AUTH_APP_USERNAME = 'profile_config/auth/app_username';
    const XML_PATH_AUTH_APP_PASSWORD = 'profile_config/auth/app_password';

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
     * InitialSetup constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return md5(
            'profile_config' . $this->scopeConfig->getValue(self::XML_PATH_AUTH_APP_URL, ScopeInterface::SCOPE_STORE)
        );
    }

    /**
     * @return bool
     */
    public function isDisplayed()
    {
        return !$this->scopeConfig->getValue(self::XML_PATH_AUTH_APP_URL, ScopeInterface::SCOPE_STORE)
            && !$this->scopeConfig->getValue(self::XML_PATH_AUTH_APP_USERNAME, ScopeInterface::SCOPE_STORE)
            && !$this->scopeConfig->getValue(self::XML_PATH_AUTH_APP_PASSWORD, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return Phrase|string
     */
    public function getText()
    {
        return __('Please configure <a href="%1">SoftCommerce</a> module.', $this->getLink());
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->urlBuilder->getUrl('adminhtml/system_config/edit', ['section' => 'plenty_core']);
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        return MessageInterface::SEVERITY_CRITICAL;
    }
}
