<?php

namespace Unzer\Core\BusinessLogic\Domain\OrderManagement\Models;

use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use UnzerSDK\Resources\TransactionTypes\Cancellation;

/**
 * Class CancelResult.
 *
 * Outcome of an order cancellation: the cancellation of the authorization sent to Unzer, or the reason nothing was
 * sent.
 *
 * @package Unzer\Core\BusinessLogic\Domain\OrderManagement\Models
 */
final class CancelResult
{
    /** @var string The authorization was cancelled at Unzer. */
    public const CANCELED = 'canceled';

    /** @var string Nothing was sent to Unzer. */
    public const SKIPPED = 'skipped';

    /** @var string */
    private string $status;

    /** @var string|null */
    private ?string $skipReason;

    /** @var Cancellation|null */
    private ?Cancellation $cancellation;

    /** @var TransactionHistory|null */
    private ?TransactionHistory $transactionHistory;

    /**
     * @param string $status
     * @param string|null $skipReason
     * @param Cancellation|null $cancellation
     * @param TransactionHistory|null $transactionHistory
     */
    private function __construct(
        string $status,
        ?string $skipReason,
        ?Cancellation $cancellation,
        ?TransactionHistory $transactionHistory
    ) {
        $this->status = $status;
        $this->skipReason = $skipReason;
        $this->cancellation = $cancellation;
        $this->transactionHistory = $transactionHistory;
    }

    /**
     * @param Cancellation $cancellation The cancellation Unzer created.
     * @param TransactionHistory $transactionHistory The history the cancellation was decided on.
     *
     * @return self
     */
    public static function canceled(Cancellation $cancellation, TransactionHistory $transactionHistory): self
    {
        return new self(self::CANCELED, null, $cancellation, $transactionHistory);
    }

    /**
     * @param string $reason One of the CancelSkipReason constants.
     * @param TransactionHistory|null $transactionHistory Null when the payment was not found.
     *
     * @return self
     */
    public static function skipped(string $reason, ?TransactionHistory $transactionHistory): self
    {
        return new self(self::SKIPPED, $reason, null, $transactionHistory);
    }

    /**
     * @return bool True when the authorization was cancelled at Unzer.
     */
    public function isCanceled(): bool
    {
        return $this->status === self::CANCELED;
    }

    /**
     * @return string One of CANCELED, SKIPPED.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string|null One of the CancelSkipReason constants when skipped.
     */
    public function getSkipReason(): ?string
    {
        return $this->skipReason;
    }

    /**
     * @return Cancellation|null
     */
    public function getCancellation(): ?Cancellation
    {
        return $this->cancellation;
    }

    /**
     * @return float The cancelled amount in currency units, 0 when skipped.
     */
    public function getCanceledAmount(): float
    {
        return $this->cancellation ? round((float)$this->cancellation->getAmount(), 4) : 0.0;
    }

    /**
     * @return TransactionHistory|null
     */
    public function getTransactionHistory(): ?TransactionHistory
    {
        return $this->transactionHistory;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'skipReason' => $this->skipReason,
            'cancellationId' => $this->cancellation ? $this->cancellation->getId() : null,
            'canceledAmount' => $this->getCanceledAmount(),
        ];
    }
}
