<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Strategy;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Factory\InlinePaymentFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePayment;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Exceptions\UnzerApiException;
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

    /**
     * @param InlinePaymentCreateContext $context
     * @param PaymentMethodConfig|null $config
     * @param Resources $resources
     *
     * @return InlinePayment
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    public function execute(
        InlinePaymentCreateContext $context,
        ?PaymentMethodConfig $config,
        Resources $resources
    ): InlinePayment {
        $chargeRequest = $this->inlinePaymentFactory->create($context, $config);
        $charge = new Charge($chargeRequest->getAmount()->getPriceInCurrencyUnits(),
            $chargeRequest->getAmount()->getCurrency(), $chargeRequest->getReturnUrl());
        $charge->setOrderId($context->getOrderId());
        $metadata = $this->unzerFactory->makeUnzerAPI()->fetchMetadata($resources->getMetadataId());
        $chargeResponse = $this->unzerFactory->makeUnzerAPI()->performCharge($charge, $chargeRequest->getPaymentType(),
            $resources->getCustomerId(), $metadata);

        return new InlinePayment($chargeResponse);
    }
}
