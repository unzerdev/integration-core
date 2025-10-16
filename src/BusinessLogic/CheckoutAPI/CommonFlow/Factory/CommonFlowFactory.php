<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Factory;

use Unzer\Core\BusinessLogic\ApiFacades\Aspects\ErrorHandlingAspect;
use Unzer\Core\BusinessLogic\ApiFacades\Aspects\StoreContextAspect;
use Unzer\Core\BusinessLogic\Bootstrap\Aspect\Aspects;
use Unzer\Core\BusinessLogic\CheckoutAPI\InlinePayment\Controller\CheckoutInlinePaymentController;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Controller\CheckoutPaymentPageController;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;

class CommonFlowFactory
{
    protected const INLINE_FLOW = 'inline_flow';
    protected const PAYMENT_PAGE_FLOW = 'payment_page_flow';


    protected const METHODS_DEFAULT_FLOWS = [
        PaymentMethodTypes::EPS => self::INLINE_FLOW,
        PaymentMethodTypes::IDEAL => self::INLINE_FLOW,
        PaymentMethodTypes::UNZER_PREPAYMENT => self::INLINE_FLOW,
        PaymentMethodTypes::PAYPAL => self::INLINE_FLOW,
        PaymentMethodTypes::WECHATPAY => self::INLINE_FLOW,
        PaymentMethodTypes::TWINT => self::INLINE_FLOW,
        PaymentMethodTypes::PRZELEWY24 => self::INLINE_FLOW,
        PaymentMethodTypes::BANCONTACT => self::INLINE_FLOW,
        PaymentMethodTypes::DIRECT_BANK_TRANSFER => self::INLINE_FLOW,
        PaymentMethodTypes::WERO => self::INLINE_FLOW,
        PaymentMethodTypes::PAYU => self::INLINE_FLOW,
        PaymentMethodTypes::POST_FINANCE_CARD => self::INLINE_FLOW,
        PaymentMethodTypes::POST_FINANCE_EFINANCE => self::INLINE_FLOW,
    ];


    /**
     * @param string $storeId
     * @param string $paymentType
     *
     * @return CheckoutInlinePaymentController|CheckoutPaymentPageController
     */
    public static function make(string $storeId, string $paymentType)
    {
        $pipeline = Aspects::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId));

        $flow = static::getPaymentFlow($paymentType);
        if ($flow === static::INLINE_FLOW) {
            return $pipeline->beforeEachMethodOfService(CheckoutInlinePaymentController::class);
        }

        return $pipeline->beforeEachMethodOfService(CheckoutPaymentPageController::class);
    }

    /**
     * @param string $paymentMethodType
     *
     * @return string
     */
    protected static function getPaymentFlow(string $paymentMethodType): string
    {
        if (array_key_exists($paymentMethodType, static::METHODS_DEFAULT_FLOWS)) {
            return static::METHODS_DEFAULT_FLOWS[$paymentMethodType];
        }

        return static::PAYMENT_PAGE_FLOW;
    }
}
