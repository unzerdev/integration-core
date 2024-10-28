<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\Processors\LineItemsProcessor;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;

/**
 * Class MockBasketLIneItemsProcessor
 *
 * @package BusinessLogic\Common\Mocks
 */
class MockBasketLIneItemsProcessor implements LineItemsProcessor
{
    public function process(Basket $basket, PaymentPageCreateContext $context): void
    {
        $basket->addBasketItem(new BasketItem(
            'Test item', $basket->getTotalValueGross(), $basket->getTotalValueGross()
        ));
    }
}
