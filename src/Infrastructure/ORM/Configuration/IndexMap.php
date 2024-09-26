<?php

namespace Unzer\Core\Infrastructure\ORM\Configuration;

/**
 * Class IndexMap.
 *
 * @package Unzer\Core\Infrastructure\ORM\Configuration
 */
class IndexMap
{
    /**
     * Array of indexed columns.
     *
     * @var Index[]
     */
    private array $indexes = [];

    /**
     * Adds boolean index.
     *
     * @param string $name Column name for index.
     *
     * @return self This instance for chaining.
     */
    public function addBooleanIndex(string $name): IndexMap
    {
        return $this->addIndex(new Index(Index::BOOLEAN, $name));
    }

    /**
     * Adds datetime index.
     *
     * @param string $name Column name for index.
     *
     * @return self This instance for chaining.
     */
    public function addDateTimeIndex(string $name): IndexMap
    {
        return $this->addIndex(new Index(Index::DATETIME, $name));
    }

    /**
     * Adds double index.
     *
     * @param string $name Column name for index.
     *
     * @return self This instance for chaining.
     */
    public function addDoubleIndex(string $name): IndexMap
    {
        return $this->addIndex(new Index(Index::DOUBLE, $name));
    }

    /**
     * Adds integer index.
     *
     * @param string $name Column name for index.
     *
     * @return self This instance for chaining.
     */
    public function addIntegerIndex(string $name): IndexMap
    {
        return $this->addIndex(new Index(Index::INTEGER, $name));
    }

    /**
     * Adds string index.
     *
     * @param string $name Column name for index.
     *
     * @return self This instance for chaining.
     */
    public function addStringIndex(string $name): IndexMap
    {
        return $this->addIndex(new Index(Index::STRING, $name));
    }

    /**
     * Returns array of indexes.
     *
     * @return Index[] Array of indexes.
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * Adds index to map.
     *
     * @param Index $index Index to be added.
     *
     * @return self This instance for chaining.
     */
    protected function addIndex(Index $index): IndexMap
    {
        $this->indexes[$index->getProperty()] = $index;

        return $this;
    }
}
