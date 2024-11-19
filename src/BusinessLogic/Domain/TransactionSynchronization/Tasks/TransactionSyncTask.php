<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Tasks;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Service\TransactionSynchronizerService;
use Unzer\Core\Infrastructure\TaskExecution\Task;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\Serializer\Interfaces\Serializable;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class TransactionSyncTask.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Tasks
 */
class TransactionSyncTask extends Task
{
    /**
     * @var TransactionHistory[]
     */
    protected array $orderIds;

    /**
     * @var string
     */
    protected string $storeId;

    /**
     * @param string[] $orderIDs
     */
    public function __construct(array $orderIDs)
    {
        $this->orderIds = $orderIDs;
        $this->storeId = StoreContext::getInstance()->getStoreId();
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public static function fromArray(array $array): Serializable
    {
        return StoreContext::doWithStore($array['storeId'], static function () use ($array) {
            return new static($array['orderIds']);
        });
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'storeId' => $this->storeId,
            'orderIds' => $this->orderIds,
        ];
    }

    /**
     * @inheritDocs
     */
    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * @inheritDocs
     */
    public function __unserialize($data): void
    {
        $this->orderIds = $data['orderIds'] ?? [];
        $this->storeId = $data['storeId'];
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function execute(): void
    {
        StoreContext::doWithStore(
            $this->storeId,
            function () {
                $this->doExecute();
            }
        );
    }

    /**
     * @return void
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidCurrencyCode
     * @throws UnzerApiException
     * @throws CurrencyMismatchException
     */
    protected function doExecute(): void
    {
        foreach ($this->orderIds as $orderId) {
            try {
                $this->getTransactionSynchronizerService()->synchronizeTransactions($orderId);
            }catch (TransactionHistoryNotFoundException $exception) {
            }

            $this->reportAlive(true);
        }

        $this->reportProgress(100);
    }

    /**
     * Returns an instance of the TransactionSynchronizerService.
     *
     * @return TransactionSynchronizerService
     */
    protected function getTransactionSynchronizerService(): TransactionSynchronizerService
    {
        return ServiceRegister::getService(TransactionSynchronizerService::class);
    }
}
