<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\BasketRequired;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\BookingAuthorizeSupport;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\BookingChargeSupport;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodNames;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;

/**
 * Class GetPaymentConfigResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response
 */
class GetPaymentConfigResponse extends Response
{
    /**
     * @var PaymentMethodConfig
     */
    private PaymentMethodConfig $paymentMethodConfig;

    /**
     * @param PaymentMethodConfig $paymentMethodConfig
     */
    public function __construct(PaymentMethodConfig $paymentMethodConfig)
    {
        $this->paymentMethodConfig = $paymentMethodConfig;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $array = [];

        $array['type'] = $this->paymentMethodConfig->getType();
        $array['typeName'] = PaymentMethodNames::PAYMENT_METHOD_NAMES[$this->paymentMethodConfig->getType()];
        $array['name'] = $this->paymentMethodConfig->getName() ? TranslationCollection::translationsToArray
        ($this->paymentMethodConfig->getName()) : [];
        $array['description'] = $this->paymentMethodConfig->getDescription()
            ? TranslationCollection::translationsToArray($this->paymentMethodConfig->getDescription()) : [];
        $array['bookingAvailable'] =
            in_array($this->paymentMethodConfig->getType(), BookingAuthorizeSupport::SUPPORTS_AUTHORIZE) &&
            in_array($this->paymentMethodConfig->getType(), BookingChargeSupport::SUPPORTS_CHARGE);
        $array['bookingMethod'] = $this->paymentMethodConfig->getBookingMethod() ?
            $this->paymentMethodConfig->getBookingMethod()->getBookingMethod() : null;
        $array['chargeAvailable'] =
            in_array($this->paymentMethodConfig->getType(), BookingChargeSupport::SUPPORTS_CHARGE);

        $array['statusIdToCharge'] = $this->paymentMethodConfig->getStatusIdToCharge();
        $array['minOrderAmount'] = $this->paymentMethodConfig->getMinOrderAmount() ? $this->paymentMethodConfig->getMinOrderAmount()->getPriceInCurrencyUnits() : 0.0;
        $array['maxOrderAmount'] = $this->paymentMethodConfig->getMaxOrderAmount() ? $this->paymentMethodConfig->getMaxOrderAmount()->getPriceInCurrencyUnits() : 0.0;
        $array['surcharge'] = $this->paymentMethodConfig->getSurcharge() ? $this->paymentMethodConfig->getSurcharge()->getPriceInCurrencyUnits() : 0.0;
        $array['restrictedCountries'] = $this->countriesToArray($this->paymentMethodConfig->getRestrictedCountries());
        $array['displaySendBasketData'] =
            !in_array($this->paymentMethodConfig->getType(), BasketRequired::BASKET_REQUIRED);
        $array['sendBasketData'] = $this->paymentMethodConfig->isSendBasketData();

        return $array;
    }

    /**
     * @param Country[] $countries
     *
     * @return array
     */
    private function countriesToArray(array $countries): array
    {
        return array_map(function ($country) {
            return [
                'code' => $country->getCode(),
                'name' => $country->getName()
            ];
        }, $countries);
    }
}
