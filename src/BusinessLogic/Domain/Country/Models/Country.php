<?php

namespace Unzer\Core\BusinessLogic\Domain\Country\Models;

/**
 * Class Country.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Country\Models
 */
class Country
{
    /**
     * @var string
     */
    private string $code;

    /**
     * @var string
     */
    private string $name;

    /**
     * @param string $code
     * @param string $name
     */
    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
