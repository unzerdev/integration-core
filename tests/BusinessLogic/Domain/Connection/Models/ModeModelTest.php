<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\Connection\Models;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class ModeModelTest.
 *
 * @package BusinessLogic\Domain\Connection\Models
 */
class ModeModelTest extends BaseTestCase
{
    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testInvalidMode(): void
    {
        // arrange
        $this->expectException(InvalidModeException::class);
        // act

        Mode::parse('test');
        // assert
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testLiveMode(): void
    {
        // arrange
        // act
        $mode = Mode::parse('live');
        // assert
        self::assertEquals('live', $mode->getMode());
        self::assertTrue($mode->equal(Mode::live()));
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testSandboxMode(): void
    {
        // arrange
        // act
        $mode = Mode::parse('sandbox');
        // assert
        self::assertEquals('sandbox', $mode->getMode());
        self::assertTrue($mode->equal(Mode::sandbox()));
    }
}
