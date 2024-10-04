<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Country\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;

/**
 * Class GetCountriesResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Country\Response
 */
class GetCountriesResponse extends Response
{
    /**
     * @var Country[]
     */
    private array $countries;

    /**
     * @param Country[] $countries
     */
    public function __construct(array $countries)
    {
        $this->countries = $countries;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->countries as $country) {
            $array[] = [
                'code' => $country->getCode(),
                'name' => $country->getName()
            ];
        }

        return $array;
    }
}
