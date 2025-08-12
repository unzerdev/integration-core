<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Response\CommonFlowResponse;
use UnzerSDK\Resources\V2\Paypage;

/**
 * Class PaymentPageResponse
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response
 */
class PaymentPageResponse extends CommonFlowResponse
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

    /**
     * @return Paypage
     */
    public function getPaypage(): Paypage
    {
        return $this->payPage;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->getPaypage() ? $this->getPaypage()->getRedirectUrl() : '';
    }
}
