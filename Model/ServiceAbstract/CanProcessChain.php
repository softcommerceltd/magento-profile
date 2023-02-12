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
class CanProcessChain implements CanProcessChainInterface
{
    /**
     * @var CanProcessChainInterface[]
     */
    private array $chains;

    /**
     * @param array $chains
     */
    public function __construct(array $chains = [])
    {
        $this->chains = $chains;
    }

    /**
     * @inheritDoc
     */
    public function execute(Service $context, array $subjects = []): bool
    {
        $result = true;
        foreach ($this->chains as $chain) {
            if (!$chain instanceof CanProcessChainInterface) {
                throw new LocalizedException(
                    __(
                        'The chain must be an instance of "%1", "%2" given.',
                        CanProcessChainInterface::class,
                        is_object($chain) ? get_class($chain) : "unknown type"
                    )
                );
            }

            $result = $chain->execute($context, $subjects);
            if (false === $result) {
                break;
            }
        }

        return $result;
    }
}
