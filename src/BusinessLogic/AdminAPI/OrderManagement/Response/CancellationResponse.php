<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;

/**
 * Class CancellationResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response
 */
class CancellationResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [];
    }
}
