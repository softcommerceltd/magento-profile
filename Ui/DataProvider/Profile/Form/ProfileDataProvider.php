<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\DataProvider\Profile\Form;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use SoftCommerce\Profile\Model\ResourceModel\Profile\CollectionFactory;

/**
 * @inheritDoc
 */
class ProfileDataProvider extends AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param CollectionFactory $collectionFactory
     * @param PoolInterface $pool
     * @param UrlInterface $urlBuilder
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        PoolInterface $pool,
        UrlInterface $urlBuilder,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->pool = $pool;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        $this->data = parent::getData();
        $this->generateConfigData();
        $this->generateData();
        return $this->data;
    }

    /**
     * @return void
     */
    private function generateConfigData(): void
    {
        $this->data['config'] = [
            'submit_url' => $this->urlBuilder->getUrl('softcommerce/profile/save', ['_current' => true])
        ];
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function generateData(): void
    {
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }
    }

    /**
     * @inheritDoc
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
