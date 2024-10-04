<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Language\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\Language\Response\GetLanguagesResponse;
use Unzer\Core\BusinessLogic\Domain\Integration\Language\LanguageService;

/**
 * Class LanguageController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Language\Controller
 */
class LanguageController
{
    /**
     * @var LanguageService
     */
    private LanguageService $languageService;

    /**
     * @param LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * @return GetLanguagesResponse
     */
    public function getLanguages(): GetLanguagesResponse
    {
        return new GetLanguagesResponse($this->languageService->getLanguages());
    }
}
