<?php

namespace BusinessLogic\AdminAPI\PaymentPageSettings;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request\PaymentPageSettingsRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response\PaymentPageSettingsGetResponse;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions\InvalidImageUrlException;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings as PaymentPageSettingsModel;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\UploadedFile;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\UploaderServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentPageSettingsServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\V2\Paypage;

/**
 * Class PaymentPageSettingsControllerTest
 *
 * @package BusinessLogic\AdminAPI\PaymentPageSettings
 */
class PaymentPageSettingsControllerTest extends BaseTestCase
{
    /**
     * @var PaymentPageSettingsServiceMock
     */
    private PaymentPageSettingsServiceMock $paymentPageSettingsService;

    /**
     * @var UploaderServiceMock
     */
    private UploaderServiceMock $uploaderService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->uploaderService = new UploaderServiceMock();

        TestServiceRegister::registerService(
            PaymentPageSettingsService::class,
            function () {
                return $this->paymentPageSettingsService;
            }
        );

        TestServiceRegister::registerService(
            UploaderService::class,
            function () {
                return $this->uploaderService;
            }
        );

        $this->unzerService = (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test'));

        TestServiceRegister::registerService(
            UnzerFactory::class,
            function () {
                return $this->unzerService;
            }
        );

        $this->paymentPageSettingsService = new PaymentPageSettingsServiceMock(
            TestServiceRegister::getService(PaymentPageSettingsRepositoryInterface::class),
            TestServiceRegister::getService(UploaderService::class),
            TestServiceRegister::getService(UnzerFactory::class)
        );

    }

    /**
     * @return void
     * @throws InvalidTranslatableArrayException
     */
    public function testIsGetResponseSuccessful(): void
    {
        // Arrange
        $this->paymentPageSettingsService->setPaymentPageSettings(
            new PaymentPageSettingsModel(
                new UploadedFile('https://www.test.com/'),
                new UploadedFile('https://www.test2.com/'),
                TranslationCollection::fromArray([
                    ['locale' => 'default', 'value' => 'shop'],
                    ['locale' => 'en_us', 'value' => 'shop']
                ]),
                '#FFFFFF',
                '#666666',
                '#111111',
                '#555555',
                '#333333',
                '#222222',
            )
        );

        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->getPaymentPageSettings();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     *
     * @throws InvalidTranslatableArrayException
     */
    public function testGetResponse(): void
    {
        // Arrange
        $settings = new PaymentPageSettingsModel(
            new UploadedFile('https://www.test.com/'),
            new UploadedFile('https://www.test2.com/'),
            TranslationCollection::fromArray([
                ['locale' => 'default', 'value' => 'shop'],
                ['locale' => 'en_us', 'value' => 'shop']
            ]),
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222'
        );

        $this->paymentPageSettingsService->setPaymentPageSettings($settings);

        $expectedResponse = new PaymentPageSettingsGetResponse($settings);

        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->getPaymentPageSettings();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @return void
     * @throws InvalidTranslatableArrayException
     */
    public function testGetResponseToArray(): void
    {
        // Arrange
        $settings = new PaymentPageSettingsModel(
            new UploadedFile('https://www.test.com/'),
            new UploadedFile('https://www.test2.com/'),
            TranslationCollection::fromArray([
                ['locale' => 'default', 'value' => 'shop'],
                ['locale' => 'en_us', 'value' => 'shop']
            ]),
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
        );

        $this->paymentPageSettingsService->setPaymentPageSettings($settings);

        $expectedResponse = new PaymentPageSettingsModel(
            new UploadedFile('https://www.test.com/'),
            new UploadedFile('https://www.test2.com/'),
            TranslationCollection::fromArray([
                ['locale' => 'default', 'value' => 'shop'],
                ['locale' => 'en_us', 'value' => 'shop']
            ]),
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
        );

        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->getPaymentPageSettings();

        // Assert
        self::assertEquals($response->toArray(), $this->expectedToArrayResponse($expectedResponse));
    }

    /**
     * @return void
     */
    public function testGetResponseToArrayNoSettings(): void
    {
        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->getPaymentPageSettings();

        $expectedResponse = [
            'shopName' => [],
            'logoImageUrl' => null,
            'backgroundImageUrl' => null,
            'headerColor' => null,
            'brandColor' => null,
            'textColor' => null,
            'linkColor' => null,
            'backgroundColor' => null,
            'footerColor' => null,
            'font' => null,
            'shadows' => null,
            'hideUnzerLogo' => null,
            'hideBasket' => null,
            'cornerRadius' => null
        ];
        // Assert
        self::assertEquals($response->toArray(), $expectedResponse);
    }

    /**
     * @return void
     * @throws InvalidTranslatableArrayException|InvalidImageUrlException
     */
    public function testIsPutResponseSuccessful(): void
    {
        // Arrange
        $settingsRequest = new PaymentPageSettingsRequest(
            TranslationCollection::fromArray([
                ['locale' => 'default', 'value' => 'shop'],
                ['locale' => 'en_us', 'value' => 'shop']
            ]),
            'https://www.test.com/',
            null,
            'https://www.test2.com/',
            null,
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
            'Ariel',
        );

        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->savePaymentPageSettings($settingsRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     * @throws InvalidTranslatableArrayException|InvalidImageUrlException
     */
    public function testPutResponseToArray(): void
    {
        // Arrange
        $settingsRequest = new PaymentPageSettingsRequest(
            TranslationCollection::fromArray([
                ['locale' => 'default', 'value' => 'shop'],
                ['locale' => 'en_us', 'value' => 'shop']
            ]),
            new \SplFileInfo('https://www.test.com/'),
            null,
            'https://www.test.com/',
            null,
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222'
        );

        $this->uploaderService->setPath('path');

        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->savePaymentPageSettings($settingsRequest);


        // Assert
        self::assertEquals(
            $this->expectedToArrayResponse($settingsRequest->transformToDomainModel()),
            $response->toArray()
        );
    }

    /**
     * @param PaymentPageSettingsModel|null $paymentPageSettings
     *
     * @return array
     */
    private function expectedToArrayResponse(?PaymentPageSettingsModel $paymentPageSettings): array
    {
        return [
            'shopName' => $paymentPageSettings->getShopNames()->toArray(),
            'logoImageUrl' => $paymentPageSettings->getLogoFile()->getUrl(),
            'backgroundImageUrl' => $paymentPageSettings->getBackgroundFile()->getUrl(),
            'headerColor' => $paymentPageSettings->getHeaderColor(),
            'brandColor' => $paymentPageSettings->getBrandColor(),
            'textColor' => $paymentPageSettings->getTextColor(),
            'linkColor' => $paymentPageSettings->getLinkColor(),
            'backgroundColor' => $paymentPageSettings->getBackgroundColor(),
            'footerColor' => $paymentPageSettings->getFooterColor(),
            'font' => $paymentPageSettings->getFont(),
            'shadows' => $paymentPageSettings->getShadows(),
            'hideUnzerLogo' => $paymentPageSettings->getHideUnzerLogo(),
            'hideBasket' => $paymentPageSettings->getHideBasket(),
            'cornerRadius' => $paymentPageSettings->getCornerRadius()
        ];
    }

    /**
     * @return void
     * @throws InvalidTranslatableArrayException
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException|InvalidImageUrlException
     */
    public function testIsCreatePreviewPageResponseSuccessful(): void
    {
        // Arrange
        $settingsRequest = new PaymentPageSettingsRequest(
            TranslationCollection::fromArray([
                ['locale' => 'default', 'value' => 'shop'],
                ['locale' => 'en_us', 'value' => 'shop']
            ]),
            'https://www.test.com/',
            null,
            'https://www.test.com/',
            null,
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
        );

        $this->paymentPageSettingsService->setPaypage(new Paypage(100, "EUR", "return"));
        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->getPaymentPagePreview($settingsRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     * @throws InvalidTranslatableArrayException|InvalidImageUrlException
     */
    public function testPaymentPagePreviewResponseToArray(): void
    {
        // Arrange
        $settingsRequest = new PaymentPageSettingsRequest(
            TranslationCollection::fromArray([
                ['locale' => 'default', 'value' => 'shop'],
                ['locale' => 'en_us', 'value' => 'shop']
            ]),
            'https://www.test.com/',
            null,
            null,
            null,
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
        );

        $id = "Id";
        $paypage = new Paypage(100, "EUR", "return");
        $paypage->setId($id);
        $this->paymentPageSettingsService->setPaypage($paypage);

        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->getPaymentPagePreview($settingsRequest);

        // Assert
        self::assertEquals(['paypageId' => $id], $response->toArray());
    }
}
