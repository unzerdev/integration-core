<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\CancelResult;

/**
 * Class CancellationResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response
 */
class CancellationResponse extends Response
{
    /** @var CancelResult|null */
    private ?CancelResult $result;

    /**
     * @param CancelResult|null $result
     */
    public function __construct(?CancelResult $result = null)
    {
        $this->result = $result;
    }

    /**
     * @return CancelResult|null The cancellation sent to Unzer, or the reason nothing was sent.
     */
    public function getResult(): ?CancelResult
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
