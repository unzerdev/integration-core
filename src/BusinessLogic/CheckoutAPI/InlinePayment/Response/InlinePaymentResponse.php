<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\InlinePayment\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePayment;

class InlinePaymentResponse extends Response
{

    private InlinePayment $inlinePayment;

    public function __construct(InlinePayment $inlinePayment)
    {
        $this->inlinePayment = $inlinePayment;
    }

    /**
     * @return InlinePayment
     */
    public function getInlinePayment(): InlinePayment
    {
        return $this->inlinePayment;
    }

    public function toArray(): array
    {
        return [];
    }


}