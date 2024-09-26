<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility;

use DateInterval;
use DateTime;
use Exception;
use Unzer\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class TestTimeProvider.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility
 */
class TestTimeProvider extends TimeProvider
{
    /** @var DateTime */
    private DateTime $time;
    
    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     *
     * TestTimeProvider constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->setCurrentLocalTime(new DateTime());
    }

    /**
     * Setup time that will be returned with get method
     *
     * @param DateTime $time
     */
    public function setCurrentLocalTime(DateTime $time): void
    {
        $this->time = $time;
    }

    /**
     * Returns time given as parameter for set method
     *
     * @return DateTime
     * @throws Exception
     */
    public function getCurrentLocalTime(): DateTime
    {
        return new DateTime('@' . $this->time->getTimestamp());
    }

    /**
     * @param int $sleepTime
     *
     * @throws Exception
     */
    public function sleep(int $sleepTime): void
    {
        $currentTime = $this->getCurrentLocalTime();
        $this->setCurrentLocalTime($currentTime->add(new DateInterval("PT{$sleepTime}S")));
    }
}
