<?php

namespace BusinessLogic\AdminAPI\PaymentPageSettings;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request\PaymentPageSettingsRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response\PaymentPageSettingsGetResponse;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings as PaymentPageSettingsModel;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\UploadedFile;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\UploaderServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentPageSettingsServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Paypage;

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
     */
    public function testIsGetResponseSuccessful(): void
    {
        // Arrange
        $this->paymentPageSettingsService->setPaymentPageSettings(
            new PaymentPageSettingsModel(
                new UploadedFile('file'),
                [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
                [new TranslatableLabel("Description", "en")],
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
     */
    public function testGetResponse(): void
    {
        // Arrange
        $settings = new PaymentPageSettingsModel(
            new UploadedFile('file'),
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
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
     */
    public function testGetResponseToArray(): void
    {
        // Arrange
        $settings = new PaymentPageSettingsModel(
            new UploadedFile('file'),
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
        );

        $this->paymentPageSettingsService->setPaymentPageSettings($settings);

        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->getPaymentPageSettings();

        // Assert
        self::assertEquals($response->toArray(), $this->expectedToArrayResponse($settings));
    }

    /**
     * @return void
     */
    public function testGetResponseToArrayNoSettings(): void
    {
        // Arrange
        $settings = new PaymentPageSettingsModel(new UploadedFile());
        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->getPaymentPageSettings();

        // Assert
        self::assertEquals($response->toArray(), $this->expectedToArrayResponse($settings));
    }

    /**
     * @return void
     * @throws InvalidTranslatableArrayException
     */
    public function testIsPutResponseSuccessful(): void
    {
        // Arrange
        $settingsRequest = new PaymentPageSettingsRequest(
            [['locale' => 'en', 'value' => 'Shop eng'], ['locale' => 'de', 'value' => 'Shop De']],
            [['locale' => 'en', 'value' => 'Shop2 eng'], ['locale' => 'de', 'value' => 'Shop2 De']],
            'path',
            null,
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
        );

        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->savePaymentPageSettings($settingsRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     * @throws InvalidTranslatableArrayException
     */
    public function testPutResponseToArray(): void
    {
        // Arrange
        $settingsRequest = new PaymentPageSettingsRequest(
            [['locale' => 'en', 'value' => 'Shop eng'], ['locale' => 'de', 'value' => 'Shop De']],
            [['locale' => 'en', 'value' => 'Shop2 eng'], ['locale' => 'de', 'value' => 'Shop2 De']],
            new \SplFileInfo('path'),
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
        self::assertEquals(['success' => true], $response->toArray());
    }

    /**
     * @param PaymentPageSettingsModel|null $paymentPageSettings
     *
     * @return array
     */
    private function expectedToArrayResponse(?PaymentPageSettingsModel $paymentPageSettings): array
    {
        return [
            'shopName' => $this->translatableLabelsToArray($paymentPageSettings->getShopName()),
            'shopTagline' => $this->translatableLabelsToArray($paymentPageSettings->getShopTagline()),
            'logoImageUrl' => $paymentPageSettings->getFile()->getUrl(),
            'headerBackgroundColor' => $paymentPageSettings->getHeaderBackgroundColor(),
            'headerFontColor' => $paymentPageSettings->getHeaderFontColor(),
            'shopNameBackgroundColor' => $paymentPageSettings->getShopNameBackgroundColor(),
            'shopNameFontColor' => $paymentPageSettings->getShopNameFontColor(),
            'shopTaglineBackgroundColor' => $paymentPageSettings->getShopTaglineBackgroundColor(),
            'shopTaglineFontColor' => $paymentPageSettings->getShopTaglineFontColor(),
        ];
    }

    /**
     * @param TranslatableLabel[] $labels
     *
     * @return array
     */
    private function translatableLabelsToArray(array $labels): array
    {
        return array_map(function ($label) {
            return [
                'locale' => $label->getCode(),
                'value' => $label->getMessage()
            ];
        }, $labels);
    }


    /**
     * @return void
     * @throws InvalidTranslatableArrayException
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    public function testIsCreatePreviewPageResponseSuccessful(): void
    {
        // Arrange
        $settingsRequest = new PaymentPageSettingsRequest(
            [['locale' => 'en', 'value' => 'Shop eng'], ['locale' => 'de', 'value' => 'Shop De']],
            [['locale' => 'en', 'value' => 'Shop2 eng'], ['locale' => 'de', 'value' => 'Shop2 De']],
            'path',
            null,
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
        );

        $this->paymentPageSettingsService->setPaypage(new Paypage(100,"EUR","return"));
        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->getPaymentPagePreview($settingsRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    public function testPaymentPagePreviewResponseToArray(): void
    {
        // Arrange
        $settingsRequest = new PaymentPageSettingsRequest(
            [['locale' => 'en', 'value' => 'Shop eng'], ['locale' => 'de', 'value' => 'Shop De']],
            [['locale' => 'en', 'value' => 'Shop2 eng'], ['locale' => 'de', 'value' => 'Shop2 De']],
            'path',
            null,
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
        );

        $id = "Id";
        $paypage = new Paypage(100,"EUR","return");
        $paypage->setId($id);
        $this->paymentPageSettingsService->setPaypage($paypage);

        // Act
        $response = AdminAPI::get()->paymentPageSettings('1')->getPaymentPagePreview($settingsRequest);

        // Assert
        self::assertEquals(['id' => $id], $response->toArray());
    }
}