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

    private string $publicKey;

    /**
     * PaymentPageResponse constructor.
     *
     * @param Paypage $payPage
     * @param string $publicKey
     */
    public function __construct(Paypage $payPage, string $publicKey)
    {
        $this->payPage = $payPage;
        $this->publicKey = $publicKey;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->payPage->getId(),
            'redirectUrl' => $this->payPage->getRedirectUrl(),
            'publicKey' => $this->publicKey,
        ];
    }
}
