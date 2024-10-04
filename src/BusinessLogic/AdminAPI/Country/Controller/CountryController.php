<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Country\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\Country\Response\GetCountriesResponse;
use Unzer\Core\BusinessLogic\Domain\Integration\Country\CountryService;

/**
 * Class CountryController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Country\Controller
 */
class CountryController
{
    /**
     * @var CountryService
     */
    private CountryService $countryService;

    /**
     * @param CountryService $countryService
     */
    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * @return GetCountriesResponse
     */
    public function getCountries(): GetCountriesResponse
    {
        return new GetCountriesResponse($this->countryService->getCountries());
    }
}
