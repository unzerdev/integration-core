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
     * @param string $code
     * @param string $flag
     */
    public function __construct(string $code, string $flag = '')
    {
        $this->code = $code;
        $this->flag = $flag;
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
}
