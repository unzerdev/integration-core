<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents;

/**
 * Class TestService.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents
 */
class TestService implements TestServiceInterface
{
    private $instanceNumber;

    /**
     * TestService constructor.
     *
     * @param $instanceNumber
     */
    public function __construct($instanceNumber)
    {
        $this->instanceNumber = $instanceNumber;
    }

    /**
     * @return mixed
     */
    public function getInstanceNumber()
    {
        return $this->instanceNumber;
    }
}
