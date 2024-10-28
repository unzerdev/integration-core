<?php

namespace Unzer\Core\Tests\BusinessLogic\WebhookApi\Handler;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Integration\Order\OrderServiceInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services\PaymentStatusMapService;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\Webhook\Services\WebhookService;
use Unzer\Core\BusinessLogic\WebhookAPI\Handler\Request\WebhookHandleRequest;
use Unzer\Core\BusinessLogic\WebhookAPI\WebhookAPI;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\OrderServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\WebhookServiceMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class WebhookControllerApiTest.
 *
 * @package BusinessLogic\WebhookAPI\Handler
 */
class WebhookControllerApiTest extends BaseTestCase
{
    /**
     * @var WebhookService
     */
    public WebhookService $webhookService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(OrderServiceInterface::class, function () {
            return new OrderServiceMock();
        });

        $this->webhookService = new WebhookServiceMock(
            new UnzerFactoryMock(),
            TestServiceRegister::getService(TransactionHistoryService::class),
            TestServiceRegister::getService(OrderServiceInterface::class),
            TestServiceRegister::getService(PaymentStatusMapService::class)
        );

        TestServiceRegister::registerService(WebhookService::class, function () {
            return $this->webhookService;
        });
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     * @throws CurrencyMismatchException
     * @throws InvalidCurrencyCode
     * @throws ConnectionSettingsNotFoundException
     * @throws TransactionHistoryNotFoundException
     */
    public function testHandleSuccessful(): void
    {
        // Arrange

        // Act
        $response = WebhookAPI::get()->webhookHandle('1')->handle(new WebhookHandleRequest('1'));

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws CurrencyMismatchException
     * @throws InvalidCurrencyCode
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     */
    public function testToArrayNoTransactionHistory(): void
    {
        // Arrange

        // Act
        $response = WebhookAPI::get()->webhookHandle('1')->handle(new WebhookHandleRequest('1'));

        // Assert
        self::assertEmpty($response->toArray());
    }
}
