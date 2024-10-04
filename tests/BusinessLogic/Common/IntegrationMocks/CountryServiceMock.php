<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\Integration\Country\CountryService as IntegrationCountryService;

/**
 * Class CountryService.
 *
 * @package BusinessLogic\Common\IntegrationMocks
 */
class CountryServiceMock implements IntegrationCountryService
{
    /**
     * @var array
     */
    private array $countries = [];

    /**
     * @inheritDoc
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * @param Country[] $countries
     *
     * @return void
     */
    public function setCountries(array $countries): void
    {
        $this->countries = $countries;
    }
}
