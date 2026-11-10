<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\PaymentResourceListener;
use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class PaymentResourceListenerMock
 *
 * @package Unzer\Core\Tests\BusinessLogic\Common\Mocks
 */
class PaymentResourceListenerMock implements PaymentResourceListener
{
    /** @var array<int, array{context: PaymentContext, exception: UnzerApiException}> */
    public array $basketFailures = [];

    public function onBasketCreationFailed(PaymentContext $context, UnzerApiException $exception): void
    {
        $this->basketFailures[] = ['context' => $context, 'exception' => $exception];
    }
}
