<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\Component\Form;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Store;

/**
 * @inheritDoc
 */
class StoreOptions implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @param StoreRepositoryInterface $storeRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->storeRepository->getList();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $this->options = [];
            foreach ($this->storeRepository->getList() as $store) {
                if ($store->getCode() == Store::ADMIN_CODE) {
                    continue;
                }
                $website = $this->websiteRepository->getById($store->getWebsiteId());
                $this->options[] = [
                    'value' => $store->getId(),
                    'label' => "{$store->getName()} [{$website->getName()}]"
                ];
            }
        }

        return $this->options;
    }
}
