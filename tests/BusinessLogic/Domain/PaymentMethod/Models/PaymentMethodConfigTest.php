<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\PaymentMethod\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidAmountsException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class PaymentMethodConfigTest.
 *
 * @package BusinessLogic\Domain\PaymentMethod\Models
 */
class PaymentMethodConfigTest extends BaseTestCase
{
    /**
     * @return void
     * @throws InvalidAmountsException
     */
    public function testMinAmountMissingWhenThereIsMaxAmount(): void
    {
        // arrange
        $this->expectException(InvalidAmountsException::class);
        // act

        new PaymentMethodConfig(
            PaymentMethodTypes::KLARNA,
            true,
            BookingMethod::authorize(),
            false,
            null,
            null,
            '2',
            null,
            Amount::fromFloat(2.2, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('DE', 'Germany'), new Country('FR', 'France')]
        );
        // assert
    }

    /**
     * @return void
     * @throws InvalidAmountsException
     */
    public function testMaxAmountMissingWhenThereIsMinAmount(): void
    {
        // arrange
        $this->expectException(InvalidAmountsException::class);
        // act

        new PaymentMethodConfig(
            PaymentMethodTypes::KLARNA,
            true,
            BookingMethod::authorize(),
            false,
            null,
            null,
            '2',
            Amount::fromFloat(2.2, Currency::getDefault()),
            null,
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('DE', 'Germany'), new Country('FR', 'France')]
        );
        // assert
    }

    /**
     * @return void
     * @throws InvalidAmountsException
     */
    public function testMinAmountGreaterThanMax(): void
    {
        // arrange
        $this->expectException(InvalidAmountsException::class);
        // act

        new PaymentMethodConfig(
            PaymentMethodTypes::KLARNA,
            true,
            BookingMethod::authorize(),
            false,
            null,
            null,
            '2',
            Amount::fromFloat(2.2, Currency::getDefault()),
            Amount::fromFloat(1.2, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('DE', 'Germany'), new Country('FR', 'France')]
        );
        // assert
    }
}
