<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ServiceAbstract;

use Magento\Framework\Exception\LocalizedException;

/**
 * @inheritDoc
 */
class ProcessorGuard implements ProcessorGuardInterface
{
    /**
     * @var ProcessorGuardInterface[]
     */
    private array $guards;

    /**
     * @param array $guards
     */
    public function __construct(array $guards = [])
    {
        $this->guards = $guards;
    }

    /**
     * @inheritDoc
     */
    public function allows(Service $context, array $subjects = []): bool
    {
        $result = true;
        foreach ($this->guards as $guard) {
            if (!$guard instanceof ProcessorGuardInterface) {
                throw new LocalizedException(
                    __(
                        'The guard must be an instance of "%1", "%2" given.',
                        ProcessorGuardInterface::class,
                        is_object($guard) ? get_class($guard) : "unknown type"
                    )
                );
            }

            $result = $guard->allows($context, $subjects);
            if (false === $result) {
                break;
            }
        }

        return $result;
    }
}
