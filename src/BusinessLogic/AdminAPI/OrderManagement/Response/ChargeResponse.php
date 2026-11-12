<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\ChargeResult;

/**
 * Class ChargeResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response
 */
class ChargeResponse extends Response
{
    /** @var ChargeResult|null */
    private ?ChargeResult $result;

    /**
     * @param ChargeResult|null $result
     */
    public function __construct(?ChargeResult $result = null)
    {
        $this->result = $result;
    }

    /**
     * @return ChargeResult|null The charge sent to Unzer, or the reason nothing was sent.
     */
    public function getResult(): ?ChargeResult
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
