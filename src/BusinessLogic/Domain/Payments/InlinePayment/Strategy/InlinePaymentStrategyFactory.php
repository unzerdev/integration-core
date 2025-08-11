<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Strategy;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Exceptions\BookingMethodNotSupportedException;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Factory\InlinePaymentFactory;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;

class InlinePaymentStrategyFactory
{
    /**
     * @param string $mode
     * @param UnzerFactory $factory
     * @param InlinePaymentFactory $paymentFactory
     *
     * @return InlinePaymentStrategyInterface
     *
     * @throws BookingMethodNotSupportedException
     */
    public function makeStrategy(string $mode, UnzerFactory $factory, InlinePaymentFactory $paymentFactory): InlinePaymentStrategyInterface
    {
        if ($mode === BookingMethod::CHARGE) {
            return new ChargePaymentStrategy($factory, $paymentFactory);
        }

        if ($mode === BookingMethod::AUTHORIZATION) {
            return new AuthorizePaymentStrategy($factory, $paymentFactory);
        }

        throw new BookingMethodNotSupportedException("Unsupported mode: $mode");
    }
}