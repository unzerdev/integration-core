<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage;

use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Interface PaymentResourceListener
 *
 * Optional integration hook for non-fatal failures while the payment resources are created.
 * Integrations that do not register one get NullPaymentResourceListener.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage
 */
interface PaymentResourceListener
{
    /**
     * Unzer refused the basket; the payment continues without it.
     *
     * @param PaymentContext $context
     * @param UnzerApiException $exception
     *
     * @return void
     */
    public function onBasketCreationFailed(PaymentContext $context, UnzerApiException $exception): void;
}
