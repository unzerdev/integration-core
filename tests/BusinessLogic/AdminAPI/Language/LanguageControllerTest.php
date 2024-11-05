<?php

namespace Unzer\Core\Tests\BusinessLogic\AdminAPI\Language;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\Domain\Integration\Language\LanguageService;
use Unzer\Core\BusinessLogic\Domain\Language\Models\Language;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\LanguageServiceMock as IntegrationMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class LanguageApiTest.
 *
 * @package BusinessLogic\AdminAPI\Language
 */
class LanguageControllerTest extends BaseTestCase
{
    /**
     * @var IntegrationMock
     */
    private IntegrationMock $languageService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->languageService = new IntegrationMock();

        TestServiceRegister::registerService(
            LanguageService::class, function () {
            return $this->languageService;
        });
    }

    /**
     * @return void
     */
    public function testGetLanguagesSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->languages('1')->getLanguages();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testGetLanguagesResponseToArray(): void
    {
        // Arrange
        $this->languageService->setLanguages([
                new Language('en'),
                new Language('fr'),
                new Language('es','es','es')
            ]
        );

        // Act
        $response = AdminAPI::get()->languages('1')->getLanguages();

        // Assert
        self::assertEquals([
            ['code' => 'en', 'flag' => '', 'name'=>''],
            ['code' => 'fr', 'flag' => '', 'name'=>''],
            ['code' => 'es', 'flag' => 'es', 'name'=>'es'],
        ], $response->toArray());
    }
}
