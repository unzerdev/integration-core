<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use Unzer\Core\BusinessLogic\Domain\Language\Models\Language;
use Unzer\Core\BusinessLogic\Domain\Integration\Language\LanguageService as IntegrationLanguageService;

/**
 * Class LanguageService.
 *
 * @package BusinessLogic\Common\IntegrationMocks
 */
class LanguageServiceMock implements IntegrationLanguageService
{
    /**
     * @var array
     */
    private array $languages = [];

    /**
     * @inheritDoc
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @param Language[] $languages
     *
     * @return void
     */
    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }
}
