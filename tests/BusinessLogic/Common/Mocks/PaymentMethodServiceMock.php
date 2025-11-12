<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;

/**
 * Class PaymentMethodServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class PaymentMethodServiceMock extends PaymentMethodService
{
    /**
     * @var PaymentMethodConfig[]
     */
    private array $paymentMethods = [];

    /**
     * @var PaymentMethodConfig|null
     */
    private ?PaymentMethodConfig $paymentMethod = null;

    /**
     * @return PaymentMethodConfig[]
     */
    public function getAllPaymentMethods(): array
    {
        return $this->paymentMethods;
    }

    /**
     * @param string $type
     * @param BookingMethod|null $systemBookingMethod
     *
     * @return ?PaymentMethodConfig
     */
    public function getPaymentMethodConfigByType(string $type, ?BookingMethod $systemBookingMethod = null): ?PaymentMethodConfig
    {
        if ($this->paymentMethod) {
            return $this->paymentMethod;
        }

        $result = array_filter($this->paymentMethods, function (PaymentMethodConfig $paymentMethod) use ($type): bool {
            return $paymentMethod->getType() === $type;
        });

        return !empty($result) ? current($result) : null;
    }

    /**
     * @param array $paymentMethods
     *
     * @return void
     */
    public function setMockPaymentMethods(array $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @param PaymentMethodConfig $paymentMethod
     *
     * @return void
     */
    public function setMockPaymentMethod(PaymentMethodConfig $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @param Amount $orderAmount
     * @param string $billingCountryIso
     *
     * @return PaymentMethodConfig[]
     */
    public function getPaymentMethodsForCheckout(Amount $orderAmount, string $billingCountryIso): array
    {
        return $this->paymentMethods;
    }
}
