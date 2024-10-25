<?php

namespace Unzer\Core\BusinessLogic\Domain\Checkout\Models;

/**
 * Class DataBag
 *
 * @package Unzer\Core\BusinessLogic\Domain\Checkout\Models
 */
class DataBag
{
    /**
     * @var array
     */
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
