<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\Language;

use Unzer\Core\BusinessLogic\Domain\Language\Models\Language;

/**
 * Interface LanguageService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\Language
 */
interface LanguageService
{
    /**
     * @return Language[]
     */
    public function getLanguages(): array;
}
