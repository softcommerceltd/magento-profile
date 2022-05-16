<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Ui\DataProvider\Modifier\Form;

/**
 * Interface MetadataPoolInterface
 */
interface MetadataPoolInterface
{
    const PATH_DELIMITER = '/';

    /**
     * @param string $componentName
     * @return array
     */
    public function get(string $componentName): array;
}
