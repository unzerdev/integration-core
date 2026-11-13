<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\RefundResult;

/**
 * Class RefundResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response
 */
class RefundResponse extends Response
{
    /** @var RefundResult|null */
    private ?RefundResult $result;

    /**
     * @param RefundResult|null $result
     */
    public function __construct(?RefundResult $result = null)
    {
        $this->result = $result;
    }

    /**
     * @return RefundResult|null The cancellations sent to Unzer, the reason nothing was sent, or a partial refund.
     */
    public function getResult(): ?RefundResult
    {
        return $this->result;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->result ? $this->result->toArray() : [];
    }
}
