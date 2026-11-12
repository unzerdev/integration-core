<?php

namespace Unzer\Core\BusinessLogic\Domain\OrderManagement\Models;

use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use UnzerSDK\Resources\TransactionTypes\Charge;

/**
 * Class ChargeResult.
 *
 * Outcome of an order charge: either the charge sent to Unzer or the reason it was skipped.
 *
 * @package Unzer\Core\BusinessLogic\Domain\OrderManagement\Models
 */
final class ChargeResult
{
    /** @var string The charge was sent to Unzer. */
    public const CHARGED = 'charged';

    /** @var string Nothing was sent to Unzer. */
    public const SKIPPED = 'skipped';

    /** @var string */
    private string $status;

    /** @var string|null */
    private ?string $skipReason;

    /** @var Charge|null */
    private ?Charge $charge;

    /** @var TransactionHistory|null */
    private ?TransactionHistory $transactionHistory;

    /**
     * @param string $status
     * @param string|null $skipReason
     * @param Charge|null $charge
     * @param TransactionHistory|null $transactionHistory
     */
    private function __construct(
        string $status,
        ?string $skipReason,
        ?Charge $charge,
        ?TransactionHistory $transactionHistory
    ) {
        $this->status = $status;
        $this->skipReason = $skipReason;
        $this->charge = $charge;
        $this->transactionHistory = $transactionHistory;
    }

    /**
     * @param Charge $charge The charge returned by Unzer.
     * @param TransactionHistory $transactionHistory The history read before the charge.
     *
     * @return self
     */
    public static function charged(Charge $charge, TransactionHistory $transactionHistory): self
    {
        return new self(self::CHARGED, null, $charge, $transactionHistory);
    }

    /**
     * @param string $reason One of the ChargeSkipReason constants.
     * @param TransactionHistory|null $transactionHistory The history read before the decision, if any.
     *
     * @return self
     */
    public static function skipped(string $reason, ?TransactionHistory $transactionHistory): self
    {
        return new self(self::SKIPPED, $reason, null, $transactionHistory);
    }

    /**
     * @return bool
     */
    public function isCharged(): bool
    {
        return $this->status === self::CHARGED;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getSkipReason(): ?string
    {
        return $this->skipReason;
    }

    /**
     * @return Charge|null
     */
    public function getCharge(): ?Charge
    {
        return $this->charge;
    }

    /**
     * @return TransactionHistory|null
     */
    public function getTransactionHistory(): ?TransactionHistory
    {
        return $this->transactionHistory;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'skipReason' => $this->skipReason,
            'chargeId' => $this->charge ? $this->charge->getId() : null,
        ];
    }
}
