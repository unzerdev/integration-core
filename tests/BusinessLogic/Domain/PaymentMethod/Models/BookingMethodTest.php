<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\PaymentMethod\Models;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidBookingMethodException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class BookingMethodTest.
 *
 * @package BusinessLogic\Domain\PaymentMethod\Models
 */
class BookingMethodTest extends BaseTestCase
{
    /**
     * @return void
     *
     * @throws InvalidBookingMethodException
     */
    public function testInvalidMode(): void
    {
        // arrange
        $this->expectException(InvalidBookingMethodException::class);
        // act

        BookingMethod::parse('test');
        // assert
    }

    /**
     * @return void
     * @throws InvalidBookingMethodException
     */
    public function testChargeBookingMethod(): void
    {
        // arrange

        // act
        $method = BookingMethod::parse('charge');

        // assert
        self::assertEquals('charge', $method->getBookingMethod());
        self::assertTrue($method->equal(BookingMethod::charge()));
    }

    /**
     * @return void
     *
     * @throws InvalidBookingMethodException
     */
    public function testSandboxMode(): void
    {
        // arrange
        // act
        $method = BookingMethod::parse('authorize');
        // assert
        self::assertEquals('authorize', $method->getBookingMethod());
        self::assertTrue($method->equal(BookingMethod::authorize()));
    }
}
