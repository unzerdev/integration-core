<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage;

use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class NullPaymentResourceListener
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage
 */
class NullPaymentResourceListener implements PaymentResourceListener
{
    /**
     * @inheritDoc
     */
    public function onBasketCreationFailed(PaymentContext $context, UnzerApiException $exception): void
    {
    }
}
