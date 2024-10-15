<?php

namespace Unzer\Core\Tests\BusinessLogic\PaymentMethodConfig\Repositories;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities\PaymentMethodConfig as PaymentMethodConfigEntity;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PaymentMethodConfigRepositoryTest.
 *
 * @package BusinessLogic\DataAccess\PaymentMethodConfig\Repositories
 */
class PaymentMethodConfigRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private RepositoryInterface $repository;

    /** @var PaymentMethodConfigRepositoryInterface */
    private $paymentMethodConfigRepository;

    /**
     * @throws RepositoryNotRegisteredException
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(PaymentMethodConfigEntity::getClassName());
        $this->paymentMethodConfigRepository = TestServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class);
    }

    /**
     * @throws Exception
     */
    public function testGetConfigsNoConfig(): void
    {
        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->paymentMethodConfigRepository, 'getPaymentMethodConfigs']
        );

        // assert
        self::assertEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testGetConfigsCount(): void
    {
        // arrange
        for ($i = 0; $i < 10; $i++) {
            $config = new PaymentMethodConfig("type{$i}", true);
            $entity = new PaymentMethodConfigEntity();
            $entity->setPaymentMethodConfig($config);
            $entity->setStoreId('1');
            $entity->setType("type{$i}");
            $this->repository->save($entity);
        }

        // act
        $result = StoreContext::doWithStore('1',
            [$this->paymentMethodConfigRepository, 'getPaymentMethodConfigs']);

        // assert
        self::assertCount(10, $result);
    }

    /**
     * @throws Exception
     */
    public function testEnableConfigNoConfig(): void
    {
        // arrange
        $config = new PaymentMethodConfig('eps', true);

        // act
        StoreContext::doWithStore('1',
            [$this->paymentMethodConfigRepository, 'enablePaymentMethodConfig'],
            [$config]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($config, $savedEntity[0]->getPaymentMethodConfig());
    }

    /**
     * @throws Exception
     */
    public function testEnableConfigConfigExists(): void
    {
        // arrange
        $config = new PaymentMethodConfig('eps', true);

        $configEntity = new PaymentMethodConfigEntity();
        $configEntity->setPaymentMethodConfig($config);
        $configEntity->setType('eps');
        $configEntity->setStoreId('1');
        $this->repository->save($configEntity);

        $newConfig = new PaymentMethodConfig('eps', false);

        // act
        StoreContext::doWithStore('1',
            [$this->paymentMethodConfigRepository, 'enablePaymentMethodConfig'],
            [$newConfig]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($newConfig, $savedEntity[0]->getPaymentMethodConfig());
    }

    /**
     * @throws Exception
     */
    public function testEnableConfigConfigExistsDifferentType(): void
    {
        // arrange
        $config = new PaymentMethodConfig('cards', true);

        $configEntity = new PaymentMethodConfigEntity();
        $configEntity->setPaymentMethodConfig($config);
        $configEntity->setType('cards');
        $configEntity->setStoreId('1');
        $this->repository->save($configEntity);

        $newConfig = new PaymentMethodConfig('eps', false);

        // act
        StoreContext::doWithStore('1',
            [$this->paymentMethodConfigRepository, 'enablePaymentMethodConfig'],
            [$newConfig]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($config, $savedEntity[0]->getPaymentMethodConfig());
        self::assertEquals($newConfig, $savedEntity[1]->getPaymentMethodConfig());
    }

    /**
     * @throws Exception
     */
    public function testGetPaymentConfigNoConfig(): void
    {
        // arrange

        // act
        $config = StoreContext::doWithStore(
            '1',
            [$this->paymentMethodConfigRepository, 'getPaymentMethodConfigByType'],
            ['eps']
        );

        // assert
        self::assertNull($config);
    }

    /**
     * @throws Exception
     */
    public function testGetPaymentConfig(): void
    {
        // arrange
        $config = new PaymentMethodConfig('cards', true);
        $configEntity = new PaymentMethodConfigEntity();
        $configEntity->setPaymentMethodConfig($config);
        $configEntity->setType('cards');
        $configEntity->setStoreId('1');
        $this->repository->save($configEntity);

        // act
        $fetchedConfig = StoreContext::doWithStore(
            '1',
            [$this->paymentMethodConfigRepository, 'getPaymentMethodConfigByType'],
            ['cards']
        );

        // assert
        self::assertEquals($config, $fetchedConfig);
    }

    /**
     * @throws Exception
     */
    public function testGetPaymentConfigDifferentStore(): void
    {
        // arrange
        $config = new PaymentMethodConfig('cards', true);
        $configEntity = new PaymentMethodConfigEntity();
        $configEntity->setPaymentMethodConfig($config);
        $configEntity->setType('cards');
        $configEntity->setStoreId('1');
        $this->repository->save($configEntity);

        // act
        $fetchedConfig = StoreContext::doWithStore(
            '2',
            [$this->paymentMethodConfigRepository, 'getPaymentMethodConfigByType'],
            ['cards']
        );

        // assert
        self::assertNull($fetchedConfig);
    }

    /**
     * @throws Exception
     */
    public function testGetPaymentConfigDifferentType(): void
    {
        // arrange
        $config = new PaymentMethodConfig('cards', true);
        $configEntity = new PaymentMethodConfigEntity();
        $configEntity->setPaymentMethodConfig($config);
        $configEntity->setType('cards');
        $configEntity->setStoreId('1');
        $this->repository->save($configEntity);

        // act
        $fetchedConfig = StoreContext::doWithStore(
            '2',
            [$this->paymentMethodConfigRepository, 'getPaymentMethodConfigByType'],
            ['eps']
        );

        // assert
        self::assertNull($fetchedConfig);
    }
}
