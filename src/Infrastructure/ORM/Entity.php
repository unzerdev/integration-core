<?php

namespace Unzer\Core\Infrastructure\ORM;

use InvalidArgumentException;
use Unzer\Core\Infrastructure\Data\DataTransferObject;
use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;

/**
 * Class Entity.
 *
 * @package Unzer\Core\Infrastructure\ORM
 */
abstract class Entity extends DataTransferObject
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;
    /**
     * Entity identifier.
     *
     * @var ?int
     */
    protected ?int $id = null;
    /**
     * Array of field names.
     *
     * @var array
     */
    protected array $fields = ['id'];

    /**
     * Returns full class name.
     *
     * @return string Fully qualified class name.
     */
    public static function getClassName(): string
    {
        return static::CLASS_NAME;
    }

    /**
     * Transforms raw array data to this entity instance.
     *
     * @param array $data Raw array data with keys for class fields. @see self::$fields for field names.
     *
     * @return static Transformed entity object.
     */
    public static function fromArray(array $data): DataTransferObject
    {
        $instance = new static();
        $instance->inflate($data);

        return $instance;
    }

    /**
     * Returns entity configuration object.
     *
     * @return EntityConfiguration Configuration object.
     */
    abstract public function getConfig(): EntityConfiguration;

    /**
     * Sets raw array data to this entity instance properties.
     *
     * @param array $data Raw array data with keys for class fields. @see self::$fields for field names.
     */
    public function inflate(array $data)
    {
        foreach ($this->fields as $fieldName) {
            $this->$fieldName = static::getArrayValue($data, $fieldName, $this->$fieldName);
        }
    }

    /**
     * Transforms entity to its array format representation.
     *
     * @return array Entity in array format.
     */
    public function toArray(): array
    {
        $data = ['class_name' => static::getClassName()];
        foreach ($this->fields as $fieldName) {
            $data[$fieldName] = $this->$fieldName;
        }

        return $data;
    }

    /**
     * Gets entity identifier.
     *
     * @return int|null Identifier.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets entity identifier.
     *
     * @param int $id entity identifier.
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Gets instance value for given index key.
     *
     * @param string $indexKey Name of index column.
     *
     * @return mixed Value for index.
     */
    public function getIndexValue(string $indexKey)
    {
        $methodName = 'get' . ucfirst($indexKey);
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        $methodName = 'is' . ucfirst($indexKey);
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        if (property_exists($this, $indexKey)) {
            return $this->$indexKey;
        }

        throw new InvalidArgumentException('Neither field not getter found for index "' . $indexKey . '".');
    }

    /**
     * Gets value from the array for given key.
     *
     * @param array $search An array with keys to check.
     * @param string $key Key to get value for.
     * @param mixed $default Default value if key is not present. NULL by default.
     *
     * @return mixed Value from the array for given key if key exists; otherwise, $default value.
     */
    protected static function getArrayValue(array $search, string $key, $default = null)
    {
        return array_key_exists($key, $search) ? $search[$key] : $default;
    }
}
