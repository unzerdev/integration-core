<?php

namespace BusinessLogic\AdminAPI\PaymentStatusMap;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Request\SavePaymentMapRequest;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums\PaymentStatus;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces\PaymentStatusMapRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services\PaymentStatusMapService;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentStatusMapServiceMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use \Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\PaymentStatusMapServiceMock as IntegrationMock;

/**
 * Class PaymentStatusMapApiTest.
 *
 * @package BusinessLogic\AdminAPI\PaymentStatusMap
 */
class PaymentStatusMapApiTest extends BaseTestCase
{
    /**
     * @var PaymentStatusMapServiceMock
     */
    private PaymentStatusMapServiceMock $paymentStatusMapService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->paymentStatusMapService = new PaymentStatusMapServiceMock(
            TestServiceRegister::getService(PaymentStatusMapRepositoryInterface::class),
            new IntegrationMock()
        );

        TestServiceRegister::registerService(
            PaymentStatusMapService::class,
            function () {
                return $this->paymentStatusMapService;
            }
        );
    }

    /**
     * @return void
     */
    public function testIsGetResponseSuccessful(): void
    {
        // Arrange
        $this->paymentStatusMapService->savePaymentStatusMappingSettings(
            [
                PaymentStatus::PAID => '1',
                PaymentStatus::UNPAID => '2',
                PaymentStatus::FULL_REFUND => '3',
                PaymentStatus::CANCELLED => '4',
                PaymentStatus::CHARGEBACK => '5',
                PaymentStatus::COLLECTION => '6',
                PaymentStatus::PARTIAL_REFUND => '7',
                PaymentStatus::DECLINED => '8'
            ]

        );

        // Act
        $response = AdminAPI::get()->paymentStatusMap('1')->getPaymentStatusMap();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testGetResponseToArray(): void
    {
        // Arrange
        $map = [
            PaymentStatus::PAID => '1',
            PaymentStatus::UNPAID => '2',
            PaymentStatus::FULL_REFUND => '3',
            PaymentStatus::CANCELLED => '4',
            PaymentStatus::CHARGEBACK => '5',
            PaymentStatus::COLLECTION => '6',
            PaymentStatus::PARTIAL_REFUND => '7',
            PaymentStatus::DECLINED => '8'
        ];
        $this->paymentStatusMapService->savePaymentStatusMappingSettings($map);

        // Act
        $response = AdminAPI::get()->paymentStatusMap('1')->getPaymentStatusMap();

        // Assert
        self::assertEquals([
            'paid' => '1',
            'unpaid' => '2',
            'full_refund' => '3',
            'cancelled' => '4',
            'chargeback' => '5',
            'collection' => '6',
            'partial_refund' => '7',
            'declined' => '8'
        ], $response->toArray());
    }

    /**
     * @return void
     */
    public function testIsSaveResponseSuccessful(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->paymentStatusMap('1')->savePaymentStatusMap(SavePaymentMapRequest::parse([
            PaymentStatus::PAID => '1',
            PaymentStatus::UNPAID => '2',
            PaymentStatus::FULL_REFUND => '3',
            PaymentStatus::CANCELLED => '4',
            PaymentStatus::CHARGEBACK => '5',
            PaymentStatus::COLLECTION => '6',
            PaymentStatus::PARTIAL_REFUND => '7',
            PaymentStatus::DECLINED => '8'
        ]));

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testSaveResponseToArray(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->paymentStatusMap('1')->savePaymentStatusMap(SavePaymentMapRequest::parse([
            PaymentStatus::PAID => '1',
            PaymentStatus::UNPAID => '2',
            PaymentStatus::FULL_REFUND => '3',
            PaymentStatus::CANCELLED => '4',
            PaymentStatus::CHARGEBACK => '5',
            PaymentStatus::COLLECTION => '6',
            PaymentStatus::PARTIAL_REFUND => '7',
            PaymentStatus::DECLINED => '8'
        ]));

        // Assert
        self::assertEquals([], $response->toArray());
    }
}
