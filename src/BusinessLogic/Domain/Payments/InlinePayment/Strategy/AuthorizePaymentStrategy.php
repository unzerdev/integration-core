<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Strategy;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Factory\InlinePaymentFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentResponse;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;

class AuthorizePaymentStrategy implements InlinePaymentStrategyInterface
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
     * @return InlinePaymentResponse
     *
     * @throws \UnzerSDK\Exceptions\UnzerApiException
     * @throws \Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException
     */
    public function execute(
        InlinePaymentCreateContext $context,
        ?PaymentMethodConfig $config,
        Resources $resources
    ): InlinePaymentResponse {
        $chargeRequest = $this->inlinePaymentFactory->create($context, $config);
        $authorize = new Authorization($chargeRequest->getAmount()->getPriceInCurrencyUnits(), $chargeRequest->getAmount()->getCurrency(), $chargeRequest->getReturnUrl());
        $authorize->setOrderId($context->getOrderId());
        $response =  $this->unzerFactory->makeUnzerAPI()->performAuthorization($authorize, $chargeRequest->getPaymentType(), $resources->getCustomerId());

        return new InlinePaymentResponse(null, $response);
    }
}