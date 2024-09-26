<?php

namespace Unzer\Core\Tests\Infrastructure\Configuration;

use Unzer\Core\Infrastructure\Configuration\ConfigEntity;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigEntityTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\Configuration
 */
class ConfigEntityTest extends TestCase
{
    /**
     * @return void
     */
    public function testToArray()
    {
        $entity = new ConfigEntity();
        $entity->setId(1234);
        $entity->setName('test_name');
        $entity->setValue('test_value');
        $entity->setContext('123');

        $this->assertProperties($entity->toArray(), $entity);
    }

    /**
     * @return void
     */
    public function testFromArray()
    {
        $data = [
            'id' => 1234,
            'name' => 'test_name',
            'value' => 'test_value',
            'context' => '221'
        ];

        $this->assertProperties($data, ConfigEntity::fromArray($data));
    }

    /**
     * @param $expected
     * @param ConfigEntity $entity
     *
     * @return void
     */
    private function assertProperties($expected, ConfigEntity $entity)
    {
        self::assertEquals($expected['id'], $entity->getId());
        self::assertEquals($expected['name'], $entity->getName());
        self::assertEquals($expected['value'], $entity->getValue());
        self::assertEquals($expected['context'], $entity->getContext());
    }
}
