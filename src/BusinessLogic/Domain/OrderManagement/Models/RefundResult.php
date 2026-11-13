<?php

namespace Unzer\Core\BusinessLogic\Domain\OrderManagement\Models;

use Throwable;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use UnzerSDK\Resources\TransactionTypes\Cancellation;

/**
 * Class RefundResult.
 *
 * Outcome of an order refund: the cancellations sent to Unzer, the reason nothing was sent, or the cancellations
 * made before Unzer failed on a later charge.
 *
 * @package Unzer\Core\BusinessLogic\Domain\OrderManagement\Models
 */
final class RefundResult
{
    /** @var string The whole amount was refunded at Unzer. */
    public const REFUNDED = 'refunded';

    /** @var string Nothing was sent to Unzer. */
    public const SKIPPED = 'skipped';

    /** @var string Part of the amount was refunded, then Unzer failed on a later charge. */
    public const PARTIAL = 'partial';

    /** @var string */
    private string $status;

    /** @var string|null */
    private ?string $skipReason;

    /** @var Cancellation[] */
    private array $cancellations;

    /** @var Throwable|null */
    private ?Throwable $error;

    /** @var TransactionHistory|null */
    private ?TransactionHistory $transactionHistory;

    /**
     * @param string $status
     * @param string|null $skipReason
     * @param Cancellation[] $cancellations
     * @param Throwable|null $error
     * @param TransactionHistory|null $transactionHistory
     */
    private function __construct(
        string $status,
        ?string $skipReason,
        array $cancellations,
        ?Throwable $error,
        ?TransactionHistory $transactionHistory
    ) {
        $this->status = $status;
        $this->skipReason = $skipReason;
        $this->cancellations = $cancellations;
        $this->error = $error;
        $this->transactionHistory = $transactionHistory;
    }

    /**
     * @param Cancellation[] $cancellations The cancellations Unzer created for the refund.
     * @param TransactionHistory $transactionHistory The history the refund was decided on.
     *
     * @return self
     */
    public static function refunded(array $cancellations, TransactionHistory $transactionHistory): self
    {
        return new self(self::REFUNDED, null, $cancellations, null, $transactionHistory);
    }

    /**
     * @param string $reason One of the RefundSkipReason constants.
     * @param TransactionHistory|null $transactionHistory Null when the payment was not found.
     *
     * @return self
     */
    public static function skipped(string $reason, ?TransactionHistory $transactionHistory): self
    {
        return new self(self::SKIPPED, $reason, [], null, $transactionHistory);
    }

    /**
     * @param Cancellation[] $cancellations The cancellations made before the failure.
     * @param Throwable $error The Unzer error of the failed cancellation.
     * @param TransactionHistory $transactionHistory The history the refund was decided on.
     *
     * @return self
     */
    public static function partial(array $cancellations, Throwable $error, TransactionHistory $transactionHistory): self
    {
        return new self(self::PARTIAL, null, $cancellations, $error, $transactionHistory);
    }

    /**
     * @return bool True when the whole amount was refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === self::REFUNDED;
    }

    /**
     * @return string One of REFUNDED, SKIPPED, PARTIAL.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string|null One of the RefundSkipReason constants when skipped.
     */
    public function getSkipReason(): ?string
    {
        return $this->skipReason;
    }

    /**
     * @return Cancellation[]
     */
    public function getCancellations(): array
    {
        return $this->cancellations;
    }

    /**
     * @return float Sum of the cancellation amounts, in currency units.
     */
    public function getRefundedAmount(): float
    {
        $total = 0.0;

        foreach ($this->cancellations as $cancellation) {
            $total += (float)$cancellation->getAmount();
        }

        return round($total, 4);
    }

    /**
     * @return Throwable|null The Unzer error of a partial refund.
     */
    public function getError(): ?Throwable
    {
        return $this->error;
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
            'cancellationIds' => array_map(static function (Cancellation $cancellation): ?string {
                return $cancellation->getId();
            }, $this->cancellations),
            'refundedAmount' => $this->getRefundedAmount(),
        ];
    }
}
