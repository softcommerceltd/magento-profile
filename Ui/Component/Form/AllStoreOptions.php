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
use function sort;

/**
 * @inheritDoc
 */
class AllStoreOptions implements OptionSourceInterface
{
    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @param StoreRepositoryInterface $storeRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        private readonly StoreRepositoryInterface $storeRepository,
        private readonly WebsiteRepositoryInterface $websiteRepository
    ) {
    }

    /**
     * @return array
     */
    public function getAllOptions(): array
    {
        return $this->storeRepository->getList();
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function toOptionArray(): array
    {
        if (null === $this->options) {
            $this->options = [];
            foreach ($this->storeRepository->getList() as $store) {
                $storeName = $store->getCode() == Store::ADMIN_CODE ? __('All Websites') : $store->getName();
                $website = $this->websiteRepository->getById($store->getWebsiteId());
                $this->options[] = [
                    'value' => $store->getId(),
                    'label' => "{$storeName} [{$website->getName()}]"
                ];
            }
            sort($this->options);
        }

        return $this->options;
    }
}
