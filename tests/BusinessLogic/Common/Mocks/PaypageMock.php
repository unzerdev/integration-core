<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use UnzerSDK\Resources\PaymentTypes\Paypage;

/**
 * Class PaypageMock
 *
 * @package BusinessLogic\Common\Mocks
 */
class PaypageMock extends Paypage
{
    private ?string $paymentId;
    private ?string $redirectUrl;

    public function setPaymentId(?string $paymentId)
    {
        $this->paymentId = $paymentId;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setRedirectUrl(?string $redirectUrl): Paypage
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }
}
