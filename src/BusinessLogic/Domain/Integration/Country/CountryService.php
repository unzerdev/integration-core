<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\Country;

use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;

/**
 * Interface CountryService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\Country
 */
interface CountryService
{
    /**
     * @return Country[]
     */
    public function getCountries(): array;
}
