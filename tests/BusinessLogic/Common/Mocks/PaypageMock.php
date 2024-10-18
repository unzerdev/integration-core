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
    private string $redirectUrl;

    public function setRedirectUrl(string $redirectUrl): Paypage
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
