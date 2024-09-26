<?php

namespace Unzer\Core\Tests\Infrastructure\Common;

use Unzer\Core\Infrastructure\ServiceRegister;

/**
 * Class TestServiceRegister.
 *
 * @package Infrastructure\Common
 */
class TestServiceRegister extends ServiceRegister
{
    /**
     * TestServiceRegister constructor.
     *
     * @inheritdoc
     */
    public function __construct(array $services = [])
    {
        // changing visibility so that Services could be reset in tests.
        parent::__construct($services);
    }
}
