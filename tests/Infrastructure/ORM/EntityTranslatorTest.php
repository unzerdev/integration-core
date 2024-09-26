<?php

namespace Unzer\Core\Tests\Infrastructure\ORM;

use Unzer\Core\Infrastructure\ORM\Exceptions\EntityClassException;
use Unzer\Core\Infrastructure\ORM\IntermediateObject;
use Unzer\Core\Infrastructure\ORM\Utility\EntityTranslator;
use Unzer\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;

/**
 * Class EntityTranslatorTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\ORM
 */
class EntityTranslatorTest extends BaseInfrastructureTestWithServices
{
    /**
     * @return void
     *
     * @throws EntityClassException
     */
    public function testTranslateWithoutInit()
    {
        $this->expectException(EntityClassException::class);

        $intermediate = new IntermediateObject();
        $translator = new EntityTranslator();
        $translator->translate([$intermediate]);
    }

    /**
     * @return void
     *
     * @throws EntityClassException
     */
    public function testInitOnNonEntity()
    {
        $this->expectException(EntityClassException::class);

        $translator = new EntityTranslator();
        $translator->init('\Unzer\Core\Infrastructure\ORM\IntermediateObject');
    }
}
