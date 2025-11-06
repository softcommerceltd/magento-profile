<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ServiceAbstract;

use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Core\Framework\DataStorageInterface;
use SoftCommerce\Core\Framework\MessageCollectorInterface;

/**
 * Interface ServiceInterface
 * used to manage profile services.
 */
interface ServiceInterface
{
    public const SERVICE_ID = 'processor';

    /**
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void;

    /**
     * @return DataStorageInterface
     */
    public function getDataStorage(): DataStorageInterface;

    /**
     * @return MessageCollectorInterface
     */
    public function getMessageCollector(): MessageCollectorInterface;

    /**
     * @return DataStorageInterface
     */
    public function getRequestStorage();

    /**
     * @return DataStorageInterface
     */
    public function getResponseStorage();

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getProfileId(): int;

    /**
     * @param ServiceInterface $context
     * @return $this
     */
    public function init($context);

    /**
     * Set whitelist of generators to execute (only these will run)
     *
     * @param array|null $generators Array of generator names (e.g., ['relation_link', 'attribute'])
     * @return $this
     */
    public function setIncludeGenerators(?array $generators);

    /**
     * Get whitelist of generators to execute
     *
     * @return array|null Array of generator names or null if no filter
     */
    public function getIncludeGenerators(): ?array;

    /**
     * Set blacklist of generators to skip (these will not run)
     *
     * @param array|null $generators Array of generator names (e.g., ['relation_link', 'media'])
     * @return $this
     */
    public function setExcludeGenerators(?array $generators);

    /**
     * Get blacklist of generators to skip
     *
     * @return array|null Array of generator names or null if no filter
     */
    public function getExcludeGenerators(): ?array;

    /**
     * Check if a generator/processor should execute based on filters
     *
     * Implements selective execution:
     * - If includeGenerators is set (whitelist), only those generators run
     * - If excludeGenerators is set (blacklist), those generators are skipped
     * - If neither is set, all generators run (default behavior)
     *
     * @param string $generatorName The generator identifier (e.g., 'relation_link', 'attribute')
     * @return bool True if generator should execute, false otherwise
     */
    public function canExecuteGenerator(string $generatorName): bool;
}
