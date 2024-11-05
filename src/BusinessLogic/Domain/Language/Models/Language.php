<?php

namespace Unzer\Core\BusinessLogic\Domain\Language\Models;

/**
 * Class Language.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Language\Models
 */
class Language
{
    /**
     * @var string
     */
    private string $code;

    /**
     * @var string
     */
    private string $flag;

    /**
     * @var string $name
     */
    private string $name;

    /**
     * @param string $code
     * @param string $flag
     * @param string $name
     */
    public function __construct(string $code, string $flag = '', string $name = '')
    {
        $this->code = $code;
        $this->flag = $flag;
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
    public function getFlag(): string
    {
        return $this->flag;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
