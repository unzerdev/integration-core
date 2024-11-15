<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use UnzerSDK\Resources\V2\Paypage;

/**
 * Class PaymentPageResponse
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response
 */
class PaymentPageResponse extends Response
{
    private Paypage $payPage;

    /**
     * PaymentPageResponse constructor.
     *
     * @param Paypage $payPage
     */
    public function __construct(Paypage $payPage)
    {
        $this->payPage = $payPage;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->payPage->getId(),
            'redirectUrl' => $this->payPage->getRedirectUrl(),
        ];
    }
}
