<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;

abstract class CommonFlowResponse extends Response
{
    public function toArray(): array
    {
        return [];
    }

    /**
     * @return string|null
     */
    abstract public function getRedirectUrl(): ?string;
}