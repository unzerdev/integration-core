<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Models;

/**
 * Class ReturnUrls
 *
 * The four URLs the payment page sends the customer back to, one per outcome.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Models
 */
class ReturnUrls
{
    private string $success;
    private string $failure;
    private string $pending;
    private string $cancel;

    /**
     * @param string $success
     * @param string $failure
     * @param string $pending
     * @param string $cancel
     */
    public function __construct(string $success, string $failure, string $pending, string $cancel)
    {
        $this->success = $success;
        $this->failure = $failure;
        $this->pending = $pending;
        $this->cancel = $cancel;
    }

    /**
     * Uses one URL for every outcome.
     *
     * @param string $url
     *
     * @return self
     */
    public static function fromSingle(string $url): self
    {
        return new self($url, $url, $url, $url);
    }

    /**
     * @return string
     */
    public function getSuccess(): string
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getFailure(): string
    {
        return $this->failure;
    }

    /**
     * @return string
     */
    public function getPending(): string
    {
        return $this->pending;
    }

    /**
     * @return string
     */
    public function getCancel(): string
    {
        return $this->cancel;
    }
}
