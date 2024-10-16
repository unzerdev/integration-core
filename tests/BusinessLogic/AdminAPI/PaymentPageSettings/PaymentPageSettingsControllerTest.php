<?php

namespace BusinessLogic\AdminAPI\PaymentPageSettings;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request\PaymentPageSettingsRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response\PaymentPageSettingsGetResponse;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings as PaymentPageSettingsModel;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentPageSettingsServiceMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

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
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->paymentPageSettingsService = new PaymentPageSettingsServiceMock(
            TestServiceRegister::getService(PaymentPageSettingsRepositoryInterface::class),
        );

        TestServiceRegister::registerService(
            PaymentPageSettingsService::class,
            function () {
                return $this->paymentPageSettingsService;
            }
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
                [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
                [new TranslatableLabel("Description", "en")],
                '#FFFFFF',
                '#666666',
                '#111111',
                '#555555',
                '#333333',
                '#222222',
                '#000000'
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
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
            '#000000'
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
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
            '#000000'
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
        $settings = new PaymentPageSettingsModel();
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
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
            '#000000'
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
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#333333',
            '#222222',
            '#000000'
        );

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
            'logoImageUrl' => $paymentPageSettings->getLogoImageUrl(),
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
}