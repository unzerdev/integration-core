<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\PaymentType\Services;

use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentType\Factory\PaymentTypeFactory;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class PaymentTypeService
{
    protected UnzerFactory $unzerFactory;
    protected PaymentTypeFactory $paymentTypeFactory;

    /**
     * @param UnzerFactory $unzerFactory
     * @param PaymentTypeFactory $paymentTypeFactory
     */
    public function __construct(UnzerFactory $unzerFactory, PaymentTypeFactory $paymentTypeFactory)
    {
        $this->unzerFactory = $unzerFactory;
        $this->paymentTypeFactory = $paymentTypeFactory;
    }


    public function create(PaymentContext $context): BasePaymentType
    {
        return $this->unzerFactory->makeUnzerAPI()->createPaymentType($this->paymentTypeFactory->create($context));
    }
}