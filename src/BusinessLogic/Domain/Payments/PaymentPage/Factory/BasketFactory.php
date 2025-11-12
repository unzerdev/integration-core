<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Factory;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Processors\BasketProcessorsRegistry;
use UnzerSDK\Resources\Basket;

/**
 * Class Factory
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory
 */
class BasketFactory
{
    private PaymentMethodService $paymentMethodService;

    /**
     * BasketFactory constructor.
     * @param PaymentMethodService $paymentMethodService
     */
    public function __construct(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
    }

    public function create(PaymentPageCreateContext $context): ?Basket
    {
        $methodConfig = $this->paymentMethodService->getPaymentMethodConfigByType($context->getPaymentMethodType());
        if (!$methodConfig || !$methodConfig->isEnabled() || !$methodConfig->isSendBasketData()) {
            return null;
        }

        $basket = $this->initializeBasket($context);
        foreach (BasketProcessorsRegistry::getProcessors($context->getPaymentMethodType()) as $processor) {
            $processor->process($basket, $context);
        }

        return $basket;
    }

    protected function initializeBasket(PaymentPageCreateContext $context): Basket
    {
        $basket = new Basket(
            $context->getOrderId(),
            $context->getAmount()->getPriceInCurrencyUnits(),
            $context->getAmount()->getCurrency()->getIsoCode()
        );

        $basket->setTotalValueGross($context->getAmount()->getPriceInCurrencyUnits());

        return $basket;
    }
}
