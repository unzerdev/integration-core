<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use Unzer\Core\Infrastructure\Serializer\Serializer;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\Runnable;

class FakeRunnable implements Runnable
{
    private array $callHistory = [];
    private $testProperty;

    public function __construct($testProperty = null)
    {
        $this->testProperty = $testProperty;
    }

    public function getMethodCallHistory($methodName)
    {
        return !empty($this->callHistory[$methodName]) ? $this->callHistory[$methodName] : [];
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize([$this->testProperty, $this->callHistory]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        list($this->testProperty, $this->callHistory) = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data): \Unzer\Core\Infrastructure\Serializer\Interfaces\Serializable
    {
        $instance = new self($data['testProperty']);

        $instance->callHistory = $data['callHistory'];

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'testProperty' => $this->testProperty,
            'callHistory' => $this->callHistory,
        ];
    }

    /**
     * Starts runnable run logic.
     */
    public function run()
    {
        $this->callHistory['run'][] = [];
    }

    /**
     * @return mixed
     */
    public function getTestProperty()
    {
        return $this->testProperty;
    }

    /**
     * @param mixed $testProperty
     */
    public function setTestProperty($testProperty)
    {
        $this->testProperty = $testProperty;
    }
}
