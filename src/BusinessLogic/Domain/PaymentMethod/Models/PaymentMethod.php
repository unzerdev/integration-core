<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models;

/**
 * Class PaymentMethod. Used for payment methods list page display.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models
 */
class PaymentMethod
{
    /**
     * @var string
     */
    private string $type;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var bool
     */
    private bool $enabled;

    /**
     * @var string
     */
    private string $description;

    /**
     * @param string $type
     * @param string $name
     * @param bool $enabled
     * @param string $description
     */
    public function __construct(string $type, string $name, bool $enabled, string $description = '')
    {
        $this->type = $type;
        $this->name = $name;
        $this->enabled = $enabled;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
