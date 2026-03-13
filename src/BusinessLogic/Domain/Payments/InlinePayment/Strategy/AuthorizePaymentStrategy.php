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
use UnzerSDK\Resources\TransactionTypes\Authorization;

class AuthorizePaymentStrategy implements InlinePaymentStrategyInterface
{
    private UnzerFactory $unzerFactory;
    private InlinePaymentFactory $inlinePaymentFactory;

    /**
     * @param UnzerFactory $unzerFactory
     * @param InlinePaymentFactory $inlinePaymentFactory
     */
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
        $authorize = new Authorization(
            $chargeRequest->getAmount()->getPriceInCurrencyUnits(),
            $chargeRequest->getAmount()->getCurrency(), $chargeRequest->getReturnUrl()
        );
        $authorize->setOrderId($context->getOrderId());
        $metadata = $this->unzerFactory->makeUnzerAPI()->fetchMetadata($resources->getMetadataId());
        $basket = $resources->getBasketId()
            ? $this->unzerFactory->makeUnzerAPI()->fetchBasket($resources->getBasketId())
            : null;
        $response = $this->unzerFactory->makeUnzerAPI()->performAuthorization(
            $authorize,
            $chargeRequest->getPaymentType(), $resources->getCustomerId(), $metadata, $basket
        );

        return new InlinePayment(null, $response);
    }
}
