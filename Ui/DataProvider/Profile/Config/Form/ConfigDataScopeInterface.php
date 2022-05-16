<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\DataProvider\Profile\Config\Form;

/**
 * Interface ConfigDataScopeInterface
 */
interface ConfigDataScopeInterface
{
    const REQUEST_PARAMS = 'request_params';
    const REQUEST_UNSERIALIZE_DATA = 'request_unserialize_data';

    /**
     * @param string|null $path
     * @param bool $unserialized
     * @return array|int|string|mixed|null
     * @throws \Exception
     */
    public function get(?string $path = null, bool $unserialized = false);

    /**
     * @return string
     */
    public function getCurrentScope(): string;

    /**
     * @return int
     */
    public function getCurrentScopeId(): int;

    /**
     * @return bool
     */
    public function isCurrentScopeDefault(): bool;

    /**
     * @param string $path
     * @return bool
     * @throws \Exception
     */
    public function isDefaultValue(string $path): bool;
}
