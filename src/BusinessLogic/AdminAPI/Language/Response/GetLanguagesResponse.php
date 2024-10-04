<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Language\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Language\Models\Language;

/**
 * Class GetLanguagesResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Language\Response
 */
class GetLanguagesResponse extends Response
{
    /**
     * @var Language[]
     */
    private array $languages;

    /**
     * @param Language[] $languages
     */
    public function __construct(array $languages)
    {
        $this->languages = $languages;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->languages as $language) {
            $array[] = [
                'code' => $language->getCode()
            ];
        }

        return $array;
    }
}
