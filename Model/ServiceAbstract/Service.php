<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ServiceAbstract;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Core\Framework\DataStorageInterface;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageCollectorInterface;
use SoftCommerce\Core\Framework\MessageCollectorInterfaceFactory;
use SoftCommerce\Core\Model\Source\StatusInterface;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use function array_column;
use function usort;

/**
 * Abstract Service Class
 *
 * Base class for profile service management providing data storage,
 * message collection, and context management for profile operations.
 *
 * Architecture:
 * - Context: Shared service state across related operations
 * - Storage: Separate storages for general data, requests, and responses
 * - Messages: Dual system - MessageCollector (new) and MessageStorage (legacy)
 *
 * @see ServiceInterface For context interface definition
 */
abstract class Service
{
    /**
     * Service context - shared state container for related operations
     *
     * @var ServiceInterface|null
     */
    protected $context;

    /**
     * General purpose data array for service configuration and state
     *
     * @var array
     */
    protected array $data = [];

    /**
     * General purpose data storage for temporary operation data
     *
     * @var DataStorageInterface
     */
    protected $dataStorage;

    /**
     * Response storage for operation results
     *
     * @var DataStorageInterface
     */
    protected $responseStorage;

    /**
     * Request storage for pending operation data
     *
     * @var DataStorageInterface
     */
    protected $requestStorage;

    /**
     * New message collector for structured message handling
     * Replaces MessageStorage with format-agnostic collection
     *
     * @var MessageCollectorInterface
     */
    protected MessageCollectorInterface $messageCollector;

    /**
     * Profile ID for the current operation
     *
     * @var int|null
     */
    protected ?int $profileId = null;

    /**
     * Operation response data array
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Operation request data array
     *
     * @var array
     */
    protected array $request = [];

    /**
     * Service type identifier (e.g., 'item_import', 'order_export')
     *
     * @var string
     */
    protected string $typeId = '';

    /**
     * Whitelist of generators to execute (only these will run)
     * When set, only generators in this list will execute
     *
     * @var array|null
     */
    protected ?array $includeGenerators = null;

    /**
     * Blacklist of generators to skip (these will not run)
     * When set, generators in this list will be skipped
     *
     * @var array|null
     */
    protected ?array $excludeGenerators = null;

    /**
     * @var callable|null
     */
    protected $progressCallback = null;

    /**
     * @var int
     */
    protected int $totalItemCount = 0;

    /**
     * @var int
     */
    protected int $currentItemCount = 0;

    /**
     * Aggregated message statistics across all batches
     * @var array
     */
    protected array $batchMessageSummary = [];

    /**
     * Aggregated processed entity IDs across all batches
     * @var array
     */
    protected array $allProcessedIds = [];

    /**
     * Count of total processed items for reporting
     * @var int
     */
    protected int $totalProcessedCount = 0;

    /**
     * Stores actual messages from all batches for final reconstruction
     * Structure: [batch_number => [entity_id => [messages]]]
     * @var array
     */
    protected array $batchMessages = [];

    /**
     * Constructor - initializes storage instances and profile ID
     *
     * @param DataStorageInterfaceFactory $dataStorageFactory Factory for data storage instances
     * @param MessageCollectorInterfaceFactory $messageCollectorFactory Factory for message collectors
     * @param SearchCriteriaBuilder $searchCriteriaBuilder Builder for search criteria
     * @param array $data Optional initialization data (may include profile_id)
     */
    public function __construct(
        protected readonly DataStorageInterfaceFactory $dataStorageFactory,
        protected readonly MessageCollectorInterfaceFactory $messageCollectorFactory,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->data = $data;
        $this->dataStorage = $this->dataStorageFactory->create();
        $this->messageCollector = $this->messageCollectorFactory->create();
        $this->requestStorage = $this->dataStorageFactory->create();
        $this->responseStorage = $this->dataStorageFactory->create();
        $this->profileId = $data[ProfileInterface::PROFILE_ID] ?? null;
    }

    /**
     * Initialize service state - resets all storages and data arrays
     * Called before each operation to ensure clean state
     *
     * @return $this
     */
    public function initialize(): static
    {
        $this->request =
        $this->response =
            [];
        $this->dataStorage->resetData();
        $this->requestStorage->resetData();
        $this->responseStorage->resetData();
        return $this;
    }

    /**
     * Reset service state - clears all operation-specific data
     * Similar to initialize() but without returning $this
     *
     * @return void
     */
    public function resetState(): void
    {
        $this->request =
        $this->response =
            [];
        $this->dataStorage->resetData();
        $this->requestStorage->resetData();
        $this->responseStorage->resetData();
    }

    /**
     * Finalize operation - called after operation completion
     * Override in child classes to add cleanup logic
     *
     * @return $this
     */
    public function finalize(): static
    {
        return $this;
    }

    /**
     * Get service context - shared state container
     *
     * @return ServiceInterface|null
     * @throws LocalizedException If context not set
     */
    public function getContext()
    {
        if (null === $this->context) {
            throw new LocalizedException(__('Context object is not set.'));
        }

        return $this->context;
    }

    /**
     * Set service context
     *
     * @param ServiceInterface|self $context Context instance
     * @return $this
     */
    public function setContext($context): static
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get general purpose data storage
     *
     * @return DataStorageInterface
     */
    public function getDataStorage(): DataStorageInterface
    {
        return $this->dataStorage;
    }

    /**
     * Get message collector for structured message handling
     * Use this for new code instead of getMessageStorage()
     *
     * @return MessageCollectorInterface
     */
    public function getMessageCollector(): MessageCollectorInterface
    {
        return $this->messageCollector;
    }

    /**
     * Get request storage for pending operation data
     *
     * @return DataStorageInterface
     */
    public function getRequestStorage()
    {
        return $this->requestStorage;
    }

    /**
     * Get response storage for operation results
     *
     * @return DataStorageInterface
     */
    public function getResponseStorage()
    {
        return $this->responseStorage;
    }

    /**
     * Get profile ID for current operation
     *
     * @return int
     * @throws LocalizedException If profile ID not set
     */
    public function getProfileId(): int
    {
        if (!$this->profileId) {
            throw new LocalizedException(__('Profile ID is not set.'));
        }
        return (int) $this->profileId;
    }

    /**
     * Set profile ID
     *
     * @param int $profileId Profile ID
     * @return $this
     */
    public function setProfileId(int $profileId): static
    {
        $this->profileId = $profileId;
        return $this;
    }

    /**
     * Get data from internal data array
     *
     * @param int|string|null $key Optional key, returns all data if null
     * @return mixed Value for key, or all data array
     */
    protected function getData($key = null): mixed
    {
        return null !== $key
            ? ($this->data[$key] ?? null)
            : ($this->data ?: []);
    }

    /**
     * Set data in internal data array
     *
     * @param mixed $data Value to store
     * @param int|string|null $key Optional key, replaces entire array if null
     * @return $this
     */
    public function setData(mixed $data, $key = null): static
    {
        if (null !== $key) {
            $this->data[$key] = $data;
        } else {
            $this->data = is_array($data) ? $data : [$data];
        }

        return $this;
    }

    /**
     * Get service type identifier
     *
     * @return string Type ID (e.g., 'item_import', 'order_export')
     */
    public function getTypeId(): string
    {
        return $this->typeId;
    }

    /**
     * Initialize service with context and copy context data
     *
     * @param ServiceInterface|self $context Context instance
     * @return $this
     */
    public function init($context): static
    {
        $this->context = $context;
        $this->setData($context->getData());
        return $this;
    }

    /**
     * Initialize multiple service instances with shared context
     *
     * @param ServiceInterface|self $context Shared context
     * @param ServiceInterface[]|self[] $instances Service instances to initialize
     * @return void
     */
    protected function initTypeInstances($context, array $instances): void
    {
        $this->context = $context;
        foreach ($instances as $instance) {
            $instance->init($context);
        }
    }

    /**
     * Initialize and sort service instances by sort order
     *
     * @param ServiceInterface[]|self[] $services Service configuration array
     * @param bool $preserveKey Keep typeId as array key (true) or use numeric keys (false)
     * @return array Sorted service classes, optionally keyed by typeId
     */
    protected function initServices(array $services, bool $preserveKey = false): array
    {
        usort($services, function (array $a, array $b) {
            return $this->getSortOrder($a) <=> $this->getSortOrder($b);
        });

        if (false === $preserveKey) {
            return array_column($services, 'class');
        }

        $result = [];
        foreach ($services as $service) {
            if (isset($service['typeId'])) {
                $result[$service['typeId']] = $service['class'] ?? null;
            }
        }

        return $result;
    }

    /**
     * Extract sort order from service configuration
     *
     * @param array $item Service configuration with optional 'sortOrder' key
     * @return int Sort order (default: 0)
     */
    private function getSortOrder(array $item): int
    {
        return (int) ($item['sortOrder'] ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function setIncludeGenerators(?array $generators): static
    {
        $this->includeGenerators = $generators;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIncludeGenerators(): ?array
    {
        return $this->includeGenerators;
    }

    /**
     * @inheritDoc
     */
    public function setExcludeGenerators(?array $generators): static
    {
        $this->excludeGenerators = $generators;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExcludeGenerators(): ?array
    {
        return $this->excludeGenerators;
    }

    /**
     * @inheritDoc
     */
    public function canExecuteGenerator(string $generatorName): bool
    {
        // Check include list (whitelist)
        if ($this->includeGenerators) {
            return in_array($generatorName, $this->includeGenerators, true);
        }

        // Check exclude list (blacklist)
        if ($this->excludeGenerators) {
            return !in_array($generatorName, $this->excludeGenerators, true);
        }

        // Default: execute all generators
        return true;
    }

    /**
     * Aggregate messages from current batch before clearing
     *
     * Stores summary statistics instead of individual messages to maintain
     * memory efficiency while preserving important information across batches.
     *
     * Handles both entity-level messages (item/order processing) and
     * profile-level messages (batch errors, system issues).
     *
     * This method should be called BEFORE clearMessages() in batch cleanup
     * to preserve error information for final reporting.
     *
     * @return void
     */
    protected function aggregateBatchMessages(): void
    {
        $messages = $this->getMessageCollector()->getMessages();

        if (empty($messages)) {
            return;
        }

        // Store batch number for message reconstruction
        $batchNumber = count($this->batchMessageSummary) + 1;

        // Store actual messages for final reconstruction
        $this->batchMessages[$batchNumber] = $messages;

        // Initialize batch summary
        $batchSummary = [
            'total' => 0,
            'errors' => 0,
            'warnings' => 0,
            'success' => 0,
            'failed' => 0,
            'batch_level_errors' => []  // For catch block errors
        ];

        // Process messages and collect statistics
        foreach ($messages as $entityId => $entityMessages) {
            // Track processed entity IDs (skip profile-level messages)
            if (is_numeric($entityId) && $entityId !== $this->getTypeId()) {
                $this->allProcessedIds[] = (int) $entityId;
            }

            foreach ($entityMessages as $msg) {
                $batchSummary['total']++;

                $status = $msg['status'] ?? StatusInterface::SUCCESS;
                $message = $msg['message'] ?? '';

                // Detect batch-level errors (from catch block)
                $isBatchLevelError = ($entityId === $this->getTypeId());

                switch ($status) {
                    case StatusInterface::ERROR:
                        $batchSummary['errors']++;

                        // Separate handling for batch-level errors
                        if ($isBatchLevelError) {
                            // Batch-level error (e.g., from exception in catch block)
                            $batchSummary['batch_level_errors'][] = [
                                'type' => 'batch_exception',
                                'message' => $message
                            ];
                        }
                        break;

                    case StatusInterface::WARNING:
                    case StatusInterface::NOTICE:
                        $batchSummary['warnings']++;
                        break;

                    case StatusInterface::FAILED:
                        $batchSummary['failed']++;
                        break;

                    case StatusInterface::SUCCESS:
                    case StatusInterface::COMPLETE:
                        $batchSummary['success']++;
                        break;

                    case StatusInterface::INFO:
                    default:
                        // INFO and other statuses don't affect the count
                        // They're informational only
                        break;
                }
            }
        }

        // Store batch summary with timestamp for debugging
        $batchSummary['batch_number'] = $batchNumber;
        $batchSummary['timestamp'] = date('Y-m-d H:i:s');

        $this->batchMessageSummary[] = $batchSummary;
        $this->totalProcessedCount += count($messages);
    }

    /**
     * Reset aggregation properties for new import/export session
     *
     * Clears all accumulated batch statistics and processed IDs.
     * Should be called in initialize() method before starting new operation.
     *
     * @return void
     */
    protected function resetAggregation(): void
    {
        $this->batchMessageSummary = [];
        $this->batchMessages = [];
        $this->allProcessedIds = [];
        $this->totalProcessedCount = 0;
    }

    /**
     * Get aggregated batch message summary
     *
     * Returns array of batch summaries with statistics from all processed batches.
     * Each summary contains: total, errors, warnings, success, failed counts,
     * batch_number, timestamp, and any batch_level_errors.
     *
     * @return array Array of batch summaries
     */
    protected function getBatchMessageSummary(): array
    {
        return $this->batchMessageSummary;
    }

    /**
     * Get all processed entity IDs across batches
     *
     * Returns aggregated list of all entity IDs that were processed
     * during the current operation, collected from all batches.
     *
     * @return array Array of entity IDs
     */
    protected function getAllProcessedIds(): array
    {
        return $this->allProcessedIds;
    }

    /**
     * Get all stored batch messages for final reconstruction
     *
     * Returns array of messages from all batches, keyed by batch number.
     * Structure: [batch_number => [entity_id => [messages]]]
     *
     * @return array Array of batch messages
     */
    protected function getBatchMessages(): array
    {
        return $this->batchMessages;
    }

    /**
     * Build consolidated summary message for finalize() method
     *
     * Creates a comprehensive summary message based on aggregated batch statistics.
     * Returns an array with the formatted message and overall status.
     *
     * This helper method consolidates the common finalize() logic used across
     * Import/Export/Collect services to avoid code duplication.
     *
     * @param string $operationType Operation type for message (e.g., 'Import', 'Export', 'Collect')
     * @param string $entityType Entity type for message (e.g., 'orders', 'items', 'stocks')
     * @return array ['message' => string, 'status' => string, 'response_ids' => array]
     */
    protected function buildConsolidatedSummary(string $operationType, string $entityType): array
    {
        // Use aggregated IDs from all batches
        $responseIds = $this->getAllProcessedIds();

        // If nothing was processed, return early
        if (!$responseIds && empty($this->getBatchMessageSummary())) {
            return [
                'message' => null,
                'status' => StatusInterface::INFO,
                'response_ids' => []
            ];
        }

        // Calculate overall status from batch summaries
        $totalErrors = 0;
        $totalWarnings = 0;
        $totalSuccess = 0;
        $totalFailed = 0;
        $allBatchLevelErrors = [];

        foreach ($this->getBatchMessageSummary() as $batch) {
            $totalErrors += $batch['errors'] ?? 0;
            $totalWarnings += $batch['warnings'] ?? 0;
            $totalSuccess += $batch['success'] ?? 0;
            $totalFailed += $batch['failed'] ?? 0;

            // Collect batch-level errors (critical exceptions)
            if (!empty($batch['batch_level_errors'])) {
                foreach ($batch['batch_level_errors'] as $batchError) {
                    $allBatchLevelErrors[] = [
                        'batch_number' => $batch['batch_number'] ?? 'unknown',
                        'message' => $batchError['message'] ?? 'Unknown error'
                    ];
                }
            }
        }

        // Determine overall status based on aggregated results
        if ($totalErrors > 0 || $totalFailed > 0) {
            $status = StatusInterface::ERROR;
        } elseif ($totalSuccess > 0 && $totalWarnings > 0) {
            $status = StatusInterface::WARNING;
        } elseif ($totalSuccess > 0) {
            $status = StatusInterface::SUCCESS;
        } elseif ($totalWarnings > 0) {
            $status = StatusInterface::INFO;
        } else {
            $status = StatusInterface::SUCCESS;
        }

        // Build comprehensive summary message
        $totalCount = count($responseIds);
        $batchCount = count($this->getBatchMessageSummary());

        $statusLabel = match ($status) {
            StatusInterface::SUCCESS => 'SUCCESS',
            StatusInterface::WARNING => 'WARNING',
            StatusInterface::ERROR => 'ERROR',
            StatusInterface::INFO => 'INFO',
            default => 'UNKNOWN'
        };

        $message = __('%1 [%2]: %3 %4, %5 batches', $operationType, $statusLabel, $totalCount, $entityType, $batchCount);

        // Add detailed statistics if there were operations
        if ($batchCount > 0) {
            $totalProcessed = $totalSuccess + $totalWarnings + $totalErrors + $totalFailed;
            $message .= PHP_EOL . __(
                'Results: S:%1 W:%2 E:%3 F:%4 (Total: %5)',
                $totalSuccess,
                $totalWarnings,
                $totalErrors,
                $totalFailed,
                $totalProcessed
            );
        }

        // Add ID summary for debugging (compact for large sets)
        if ($totalCount > 100) {
            $message .= PHP_EOL . __('IDs: %1 ...+%2',
                implode(', ', array_slice($responseIds, 0, 5)),
                $totalCount - 5
            );
        } elseif ($totalCount > 20) {
            $message .= PHP_EOL . __('IDs: %1 ...+%2',
                implode(', ', array_slice($responseIds, 0, 10)),
                $totalCount - 10
            );
        } elseif ($totalCount > 0) {
            $message .= PHP_EOL . __('IDs: %1', implode(', ', $responseIds));
        }

        return [
            'message' => $message,
            'status' => $status,
            'response_ids' => $responseIds,
            'batch_level_errors' => $allBatchLevelErrors
        ];
    }

    /**
     * Get total count of processed items
     *
     * Returns the cumulative count of items processed across all batches.
     *
     * @return int Total processed count
     */
    protected function getTotalProcessedCount(): int
    {
        return $this->totalProcessedCount;
    }
}
