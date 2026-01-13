<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\DataBag;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Enums\PaymentPageType;

/**
 * Class PaymentPageCreateContext
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Models
 */
class PaymentPageCreateContext extends PaymentContext
{
    /** @var string $paypageType */
    private string $paypageType;

    /**
     * @param string $paymentMethodType
     * @param string $orderId
     * @param Amount $amount
     * @param string $returnUrl
     * @param DataBag $checkoutSession
     * @param string $locale
     * @param BookingMethod|null $systemBookingMethod
     * @param string $paypageType
     */
    public function __construct(
        string $paymentMethodType,
        string $orderId,
        Amount $amount,
        string $returnUrl,
        DataBag $checkoutSession,
        string $locale = 'default',
        ?BookingMethod $systemBookingMethod = null,
        string $paypageType = PaymentPageType::EMBEDDED
    ) {
        parent::__construct(
            $paymentMethodType,
            $orderId,
            $amount,
            $returnUrl,
            $checkoutSession,
            $locale,
            $systemBookingMethod
        );

        $this->paypageType = $paypageType;
    }

    /**
     * @return string
     */
    public function getPaypageType(): string
    {
        return $this->paypageType;
    }
}
