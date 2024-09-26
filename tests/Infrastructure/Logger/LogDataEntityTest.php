<?php

namespace Unzer\Core\Tests\Infrastructure\Logger;

use Unzer\Core\Infrastructure\Logger\LogData;
use PHPUnit\Framework\TestCase;

/**
 * Class LogDataEntityTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\Logger
 */
class LogDataEntityTest extends TestCase
{
    /**
     * @return void
     */
    public function testToArray()
    {
        $entity = new LogData(
            'test integration',
            2,
            time(),
            'Test',
            'test message',
            ['first key' => 'first value', 'second key' => 'second value']
        );
        $entity->setId(1234);

        $this->assertProperties($entity->toArray(), $entity);
    }

    /**
     * @return void
     */
    public function testFromArray()
    {
        $data = [
            'id' => 1234,
            'integration' => 'test integration',
            'logLevel' => 2,
            'timestamp' => time(),
            'component' => 'Test',
            'message' => 'Test message',
            'context' => ['first key' => 'first value', 'second key' => 'second value'],
        ];

        $this->assertProperties($data, LogData::fromArray($data));
    }

    /**
     * @param $expected
     * @param LogData $entity
     *
     * @return void
     */
    private function assertProperties($expected, LogData $entity)
    {
        self::assertEquals($expected['id'], $entity->getId());
        self::assertEquals($expected['integration'], $entity->getIntegration());
        self::assertEquals($expected['logLevel'], $entity->getLogLevel());
        self::assertEquals($expected['timestamp'], $entity->getTimestamp());
        self::assertEquals($expected['component'], $entity->getComponent());
        self::assertEquals($expected['message'], $entity->getMessage());

        if (isset($expected['context'])) {
            self::assertCount(count($expected['context']), $entity->getContext());
            $context = $entity->getContext();
            foreach ($context as $item) {
                self::assertArrayHasKey($item->getName(), $expected['context']);
                self::assertEquals($item->getValue(), $expected['context'][$item->getName()]);
            }
        }
    }
}
