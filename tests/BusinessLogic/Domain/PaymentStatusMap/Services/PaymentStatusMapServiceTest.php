<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\PaymentMethodStatusMap\Services;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\PaymentStatusMap\Entities\PaymentStatusMap;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums\PaymentStatus;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services\PaymentStatusMapService;
use Unzer\Core\Infrastructure\ORM\Exceptions\EntityClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PaymentStatusMapServiceTes.
 *
 * @package Unzer\Core\Tests\BusinessLogic\Domain\PaymentMethodStatusMap\Services
 */
class PaymentStatusMapServiceTest extends BaseTestCase
{
    /**
     * @var PaymentStatusMapService
     */
    public $service;

    /**
     * @var MemoryRepository
     */
    public $paymentStatusMapRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentStatusMapRepository = TestRepositoryRegistry::getRepository(PaymentStatusMap::getClassName());
        $this->service = TestServiceRegister::getService(PaymentStatusMapService::class);
    }

    /**
     * @return void
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     */
    public function testSaveMap(): void
    {
        // arrange
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

        // act
        StoreContext::doWithStore('1', [$this->service, 'savePaymentStatusMappingSettings'], [$map]);

        // assert
        $savedEntity = $this->paymentStatusMapRepository->selectOne();
        self::assertEquals($map, $savedEntity->getPaymentStatusMap());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetDefaultMap(): void
    {
        // arrange
        $expectedMap = [
            PaymentStatus::PAID => '1',
            PaymentStatus::UNPAID => '2',
            PaymentStatus::FULL_REFUND => '3',
            PaymentStatus::CANCELLED => '4',
            PaymentStatus::CHARGEBACK => '5',
            PaymentStatus::COLLECTION => '6',
            PaymentStatus::PARTIAL_REFUND => '7',
            PaymentStatus::DECLINED => '8'
        ];

        // act
        $map = StoreContext::doWithStore('1', [$this->service, 'getPaymentStatusMap']);

        // assert
        self::assertEquals($map, $expectedMap);
    }

}
