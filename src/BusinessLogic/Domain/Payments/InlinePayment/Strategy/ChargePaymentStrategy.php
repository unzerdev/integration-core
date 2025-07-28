<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Strategy;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Factory\InlinePaymentFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentResponse;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;
use UnzerSDK\Resources\TransactionTypes\Charge;

class ChargePaymentStrategy implements InlinePaymentStrategyInterface
{
    private UnzerFactory $unzerFactory;
    private InlinePaymentFactory $inlinePaymentFactory;

    public function __construct(UnzerFactory $unzerFactory, InlinePaymentFactory $inlinePaymentFactory)
    {
        $this->unzerFactory = $unzerFactory;
        $this->inlinePaymentFactory = $inlinePaymentFactory;
    }

    public function execute(
        InlinePaymentCreateContext $context,
        PaymentMethodConfig $config,
        Resources $resources
    ): InlinePaymentResponse {
        $chargeRequest = $this->inlinePaymentFactory->create($context, $config, $resources);
        $charge = new Charge($chargeRequest->getAmount()->getPriceInCurrencyUnits(), $chargeRequest->getAmount()->getCurrency(), $chargeRequest->getReturnUrl());
        $charge =  $this->unzerFactory->makeUnzerAPI()->performCharge($charge, $chargeRequest->getPaymentTypeId(), $resources->getCustomerId());

        return new InlinePaymentResponse($charge->getReturnUrl());
    }
}
