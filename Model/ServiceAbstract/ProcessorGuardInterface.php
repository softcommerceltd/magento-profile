<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ServiceAbstract;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Guard interface for validating whether processing should proceed.
 *
 * Implements the Guard Pattern to control access to processing operations.
 * Guards are typically organized in chains where each guard validates a specific
 * business rule or constraint. If any guard returns false, processing is blocked.
 *
 * Common use cases:
 * - Validate entity state (locked, pending, etc.)
 * - Check business rules (status allowed, date range valid, etc.)
 * - Verify permissions or access control
 * - Filter by configuration (store filter, channel filter, etc.)
 *
 * Guards are executed sequentially in the order defined in di.xml configuration.
 * The first guard that returns false stops the chain and blocks processing.
 *
 * Example implementation:
 * <code>
 * class IsSalesOrderStatusAllowed implements ProcessorGuardInterface
 * {
 *     public function allows(Service $context, array $subjects = []): bool
 *     {
 *         list($salesOrder) = $subjects;
 *
 *         // Allow already exported orders regardless of status
 *         if ($salesOrder->getPlentyOrderId()) {
 *             return true;
 *         }
 *
 *         // For new orders, validate status is in allowed list
 *         return in_array(
 *             $salesOrder->getStatus(),
 *             $context->orderConfig()->getProcessOrderStatusFilter()
 *         );
 *     }
 * }
 * </code>
 *
 * @api
 * @since 1.0.0
 */
interface ProcessorGuardInterface
{
    /**
     * Determines whether the processing operation is allowed to proceed.
     *
     * This method evaluates business rules, constraints, or permissions to decide
     * if the subjects can be processed. Returning false blocks processing and
     * typically triggers logging via the context's MessageCollector.
     *
     * Implementations should:
     * - Return true to allow processing to continue
     * - Return false to block processing
     * - Add descriptive messages to context MessageCollector when blocking
     * - Throw LocalizedException only for exceptional/error conditions
     *
     * @param Service $context The service context providing access to configuration,
     *                        message collector, and other processing utilities.
     * @param DataObject[] $subjects The entities being validated (e.g., Order, Customer).
     *                              Multiple subjects can be passed when validation
     *                              involves relationships between entities.
     *
     * @return bool True if processing is allowed, false to block processing.
     *
     * @throws LocalizedException When validation cannot be completed due to
     *                           configuration errors, missing dependencies, or
     *                           other exceptional conditions that prevent the
     *                           guard from making a decision.
     */
    public function allows(Service $context, array $subjects = []): bool;
}
