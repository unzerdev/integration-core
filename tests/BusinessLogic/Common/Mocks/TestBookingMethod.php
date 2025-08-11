<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidBookingMethodException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use UnzerSDK\Constants\TransactionTypes;

class TestBookingMethod extends BookingMethod
{
    /**
     * Live mode string constant.
     */
    public const AUTHORIZATION = TransactionTypes::AUTHORIZATION;

    /**
     * Sandbox mode string constant.
     */
    public const CHARGE = TransactionTypes::CHARGE;

    /**
     * @var string
     */
    private string $bookingMethod;

    /**
     * @param string $bookingMethod
     */
    private function __construct(string $bookingMethod)
    {
        $this->bookingMethod = $bookingMethod;
    }

    /**
     * Called for authorize type.
     *
     * @return BookingMethod
     */
    public static function authorize(): self
    {
        return new self(self::AUTHORIZATION);
    }

    /**
     * Called for charge mode.
     *
     * @return BookingMethod
     */
    public static function charge(): self
    {
        return new self(self::CHARGE);
    }

    /**
     * @return string
     */
    public function getBookingMethod(): string
    {
        return $this->bookingMethod;
    }

    /**
     * Returns instance of Mode based on mode string.
     *
     * @param string $mode
     *
     * @return self
     *
     * @throws InvalidBookingMethodException
     */
    public static function parse(string $mode): self
    {
        if ($mode === self::AUTHORIZATION) {
            return self::authorize();
        }

        if ($mode === self::CHARGE) {
            return self::charge();
        }

        throw new InvalidBookingMethodException(
            new TranslatableLabel(
                'Invalid booking method. Mode must be authorize or charge.',
                'paymentMethod.invalidBookingMethod'
            )
        );
    }

    /**
     * @param BookingMethod $bookingMethod
     *
     * @return bool
     */
    public function equal(BookingMethod $bookingMethod): bool
    {
        return $this->getBookingMethod() === $bookingMethod->getBookingMethod();
    }
    /**
     * Called for authorize type.
     *
     * @return BookingMethod
     */
    public static function test(): self
    {
        return new self('test');
    }
}