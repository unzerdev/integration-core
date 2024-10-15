<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethod;
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
     * @var PaymentMethod[]
     */
    private array $paymentMethods = [];

    /**
     * @var PaymentMethodConfig|null
     */
    private ?PaymentMethodConfig $paymentMethod = null;

    /**
     * @return PaymentMethod[]
     */
    public function getAllPaymentMethods(): array
    {
        return $this->paymentMethods;
    }

    /**
     * @param string $type
     *
     * @return PaymentMethodConfig
     */
    public function getPaymentMethodConfigByType(string $type): PaymentMethodConfig
    {
        return $this->paymentMethod;
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
}
