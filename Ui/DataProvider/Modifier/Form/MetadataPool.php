<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\DataProvider\Modifier\Form;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Ui\Config\Data;
use Magento\Ui\Config\Reader;
use Magento\Ui\Config\ReaderFactory;

/**
 * @inheritDoc
 */
class MetadataPool implements MetadataPoolInterface
{
    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @var array
     */
    private $data;

    /**
     * @var ReaderFactory
     */
    private ReaderFactory $readerFactory;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param CacheInterface $cache
     * @param ReaderFactory $readerFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CacheInterface $cache,
        ReaderFactory $readerFactory,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->readerFactory = $readerFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param string $componentName
     * @return array
     */
    public function get(string $componentName): array
    {
        if (!$this->data) {
            $this->initData($componentName);
        }

        return $this->data ?: [];
    }

    /**
     * @param string $componentName
     */
    private function initData(string $componentName)
    {
        $cacheId = Data::CACHE_ID . '_' . $componentName;
        $this->data = $this->cache->load($cacheId);
        if (false === $this->data) {
            /** @var Reader $reader */
            $reader = $this->readerFactory->create(
                ['fileName' => sprintf(Data::SEARCH_PATTERN, $componentName)]
            );
            $this->data = $reader->read();
            $this->cache->save($this->serializer->serialize($this->data), $cacheId);
        } else {
            $this->data = $this->serializer->unserialize($this->data);
        }
    }
}
