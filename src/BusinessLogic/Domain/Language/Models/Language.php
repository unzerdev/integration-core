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
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}
