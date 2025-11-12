<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\InlinePayment\Response;

use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Response\CommonFlowResponse;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePayment;

class InlinePaymentResponse extends CommonFlowResponse
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

    /**
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->getInlinePayment() ? $this->getInlinePayment()->getPayment()->getRedirectUrl() : '';
    }
}
