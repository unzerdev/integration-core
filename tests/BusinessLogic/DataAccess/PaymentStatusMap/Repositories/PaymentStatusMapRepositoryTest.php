<?php

namespace Unzer\Core\Tests\BusinessLogic\DataAccess\PaymentStatusMap\Repositories;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\PaymentStatusMap\Entities\PaymentStatusMap;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums\PaymentStatus;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces\PaymentStatusMapRepositoryInterface;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PaymentStatusMapRepositoryTest.
 *
 * @package Unzer\Core\Tests\BusinessLogic\DataAccess\PaymentStatusMap\Repositories
 */
class PaymentStatusMapRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private RepositoryInterface $repository;

    /** @var PaymentStatusMapRepositoryInterface */
    private $paymentStatusMapRepository;

    /**
     * @throws RepositoryNotRegisteredException
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(PaymentStatusMap::getClassName());
        $this->paymentStatusMapRepository = TestServiceRegister::getService(PaymentStatusMapRepositoryInterface::class);
    }

    /**
     * @throws Exception
     */
    public function testGetPaymentStatusMapNoStatusMap(): void
    {
        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->paymentStatusMapRepository, 'getPaymentStatusMap']
        );

        // assert
        self::assertEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testGetStatusMap(): void
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

        $settingsEntity = new PaymentStatusMap();
        $settingsEntity->setPaymentStatusMap($map);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('1',
            [$this->paymentStatusMapRepository, 'getPaymentStatusMap']);

        // assert
        self::assertEquals($map, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetMapSetForDifferentStore(): void
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

        $settingsEntity = new PaymentStatusMap();
        $settingsEntity->setPaymentStatusMap($map);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('2',
            [$this->paymentStatusMapRepository, 'getPaymentStatusMap']);

        // assert
        self::assertEmpty($result);
    }


    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetPaymentStatusMap(): void
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
        StoreContext::doWithStore('1',
            [$this->paymentStatusMapRepository, 'setPaymentStatusMap'],
            [$map]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($map, $savedEntity[0]->getPaymentStatusMap());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetSettingsAlreadyExists(): void
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

        $settingsEntity = new PaymentStatusMap();
        $settingsEntity->setPaymentStatusMap($map);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        $newMap = [
            PaymentStatus::PAID => '11',
            PaymentStatus::UNPAID => '22',
            PaymentStatus::FULL_REFUND => '33',
            PaymentStatus::CANCELLED => '44',
            PaymentStatus::CHARGEBACK => '55',
            PaymentStatus::COLLECTION => '66',
            PaymentStatus::PARTIAL_REFUND => '77',
            PaymentStatus::DECLINED => '88'
        ];

        // act
        StoreContext::doWithStore('1',
            [$this->paymentStatusMapRepository, 'setPaymentStatusMap'],
            [$newMap]
        );

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($newMap, $savedEntity->getPaymentStatusMap());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetSettingsAlreadyExistsForOtherStore()
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

        $settingsEntity = new PaymentStatusMap();
        $settingsEntity->setPaymentStatusMap($map);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $newMap = [
            PaymentStatus::PAID => '11',
            PaymentStatus::UNPAID => '22',
            PaymentStatus::FULL_REFUND => '33',
            PaymentStatus::CANCELLED => '44',
            PaymentStatus::CHARGEBACK => '55',
            PaymentStatus::COLLECTION => '66',
            PaymentStatus::PARTIAL_REFUND => '77',
            PaymentStatus::DECLINED => '88'
        ];
        // act
        StoreContext::doWithStore('2',
            [$this->paymentStatusMapRepository, 'setPaymentStatusMap'],
            [$newMap]
        );
        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(2, $savedEntity);
    }

    /**
     * @throws Exception
     */
    public function testDeletePaymentStatusMapExists(): void
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

        $settingsEntity = new PaymentStatusMap();
        $settingsEntity->setPaymentStatusMap($map);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        StoreContext::doWithStore('1', [$this->paymentStatusMapRepository, 'deletePaymentStatusMapEntity']);


        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(0,$savedEntity);
    }

    /**
     * @throws Exception
     */
    public function testDeleteSettingsNotExists(): void
    {
        // act
        StoreContext::doWithStore('1', [$this->paymentStatusMapRepository, 'deletePaymentStatusMapEntity']);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(0,$savedEntity);
    }
}
