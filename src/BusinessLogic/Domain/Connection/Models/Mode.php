<?php

namespace Unzer\Core\BusinessLogic\Domain\Connection\Models;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class Mode.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Connection\Models
 */
class Mode
{
    /**
     * Live mode string constant.
     */
    public const LIVE = 'live';

    /**
     * Sandbox mode string constant.
     */
    public const SANDBOX = 'sandbox';

    /**
     * @var string
     */
    private string $mode;

    /**
     * @param string $mode
     */
    private function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    /**
     * Called for live mode.
     *
     * @return Mode
     */
    public static function live(): self
    {
        return new self(self::LIVE);
    }

    /**
     * Called for sandbox mode.
     *
     * @return Mode
     */
    public static function sandbox(): self
    {
        return new self(self::SANDBOX);
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Returns instance of Mode based on mode string.
     *
     * @param string $mode
     *
     * @return self
     *
     * @throws InvalidModeException
     */
    public static function parse(string $mode): self
    {
        if ($mode === self::LIVE) {
            return self::live();
        }

        if ($mode === self::SANDBOX) {
            return self::sandbox();
        }

        throw new InvalidModeException(
            new TranslatableLabel(
                'Invalid mode. Mode must be live or sandbox.',
                'connection.invalidMode'
            )
        );
    }

    /**
     * @param Mode $mode
     *
     * @return bool
     */
    public function equal(Mode $mode): bool
    {
        return $this->getMode() === $mode->getMode();
    }
}
