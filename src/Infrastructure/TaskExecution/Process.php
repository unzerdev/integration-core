<?php

namespace Unzer\Core\Infrastructure\TaskExecution;

use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Unzer\Core\Infrastructure\ORM\Configuration\IndexMap;
use Unzer\Core\Infrastructure\ORM\Entity;
use Unzer\Core\Infrastructure\Serializer\Serializer;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\Runnable;
use InvalidArgumentException;

/**
 * Class Process
 * @package Unzer\Core\Infrastructure\ORM\Entities
 */
class Process extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Unique identifier.
     *
     * @var string
     */
    protected string $guid;

    /**
     * Runnable instance.
     *
     * @var Runnable
     */
    protected Runnable $runner;

    /**
     * Sets raw array data to this entity instance properties.
     *
     * @param array $data Raw array data with keys 'id', 'guid' and 'runner'.
     *
     * @throws InvalidArgumentException In case when @see $data does not have all needed keys.
     */
    public function inflate(array $data)
    {
        if (!isset($data['guid'], $data['runner'])) {
            throw new InvalidArgumentException('Data array needs to have "guid" and "runner" keys.');
        }

        parent::inflate($data);
        $this->setGuid($data['guid']);
        $this->setRunner(Serializer::unserialize($data['runner']));
    }

    /**
     * Transforms entity to its array format representation.
     *
     * @return array Entity in array format.
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['guid'] = $this->getGuid();
        $data['runner'] = Serializer::serialize($this->getRunner());

        return $data;
    }

    /**
     * Returns entity configuration object
     *
     * @return EntityConfiguration
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('guid');

        return new EntityConfiguration($indexMap, 'Process');
    }

    /**
     * Gets Guid.
     *
     * @return string Guid.
     */
    public function getGuid(): string
    {
        return $this->guid;
    }

    /**
     * Sets Guid.
     *
     * @param string $guid Guid.
     */
    public function setGuid(string $guid)
    {
        $this->guid = $guid;
    }

    /**
     * Gets Runner.
     *
     * @return Runnable Runner.
     */
    public function getRunner(): Runnable
    {
        return $this->runner;
    }

    /**
     * Sets Runner.
     *
     * @param Runnable $runner Runner.
     */
    public function setRunner(Runnable $runner)
    {
        $this->runner = $runner;
    }
}
