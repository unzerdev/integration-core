<?php

namespace BusinessLogic\AdminAPI\Country;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\Integration\Country\CountryService;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\CountryServiceMock as IntegrationMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class CountryApiTest.
 *
 * @package BusinessLogic\AdminAPI\Country
 */
class CountryApiTest extends BaseTestCase
{
    /**
     * @var IntegrationMock
     */
    private IntegrationMock $countryService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->countryService = new IntegrationMock();

        TestServiceRegister::registerService(
            CountryService::class, function () {
            return $this->countryService;
        });
    }

    /**
     * @return void
     */
    public function testGetCountriesSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->countries('1')->getCountries();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testGetCountriesResponseToArray(): void
    {
        // Arrange

        $this->countryService->setCountries([
                new Country('en', 'England'),
                new Country('fr', 'France'),
                new Country('es', 'Spain')
            ]
        );

        // Act
        $response = AdminAPI::get()->countries('1')->getCountries();

        // Assert
        self::assertEquals([
            ['code' => 'en', 'name' => 'England'],
            ['code' => 'fr', 'name' => 'France'],
            ['code' => 'es', 'name' => 'Spain']
        ], $response->toArray());
    }
}
