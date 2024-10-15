<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\PaymentMethod\Services;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities\PaymentMethodConfig as PaymentMethodConfigEntity;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodNames;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidPaymentTypeException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\KeypairMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PaymentMethodServiceTest.
 *
 * @package BusinessLogic\Domain\PaymentMethod\Services
 */
class PaymentMethodServiceTest extends BaseTestCase
{
    /**
     * @var PaymentMethodService
     */
    public $service;

    /**
     * @var MemoryRepository
     */
    public $paymentMethodConfigRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentMethodConfigRepository = TestRepositoryRegistry::getRepository(PaymentMethodConfigEntity::getClassName());
        $this->service = TestServiceRegister::getService(PaymentMethodService::class);
        UnzerFactoryMock::getInstance();
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testNoPaymentMethods(): void
    {
        // arrange
        $this->mockData('s-pub-test', 's-priv-test');

        // act
        $methods = StoreContext::doWithStore('1', [$this->service, 'getAllPaymentMethods']);

        // assert
        self::assertEmpty($methods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsNoConfigs(): void
    {
        // arrange
        $this->mockData('s-pub-test', 's-priv-test', ['eps', 'googlepay', 'card', 'test']);

        // act
        $methods = StoreContext::doWithStore('1', [$this->service, 'getAllPaymentMethods']);

        // assert

        $expectedMethods = [
            new PaymentMethod('eps', PaymentMethodNames::PAYMENT_METHOD_NAMES['eps'], false),
            new PaymentMethod('googlepay', PaymentMethodNames::PAYMENT_METHOD_NAMES['googlepay'], false),
            new PaymentMethod('card', PaymentMethodNames::PAYMENT_METHOD_NAMES['card'], false),
            new PaymentMethod('test', PaymentMethodNames::DEFAULT_PAYMENT_METHOD_NAME, false),
        ];

        self::assertCount(4, $methods);
        self::assertEquals($expectedMethods, $methods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethods(): void
    {
        // arrange

        $this->setEntities(
            [
                new PaymentMethodConfig('przelewy24', true),
                new PaymentMethodConfig('giropay', false),
                new PaymentMethodConfig('twint', true),
                new PaymentMethodConfig('wechatpay', true),
            ]
        );

        $this->mockData('s-pub-test', 's-priv-test', ['przelewy24', 'giropay', 'twint', 'wechatpay']);

        // act
        $methods = StoreContext::doWithStore('1', [$this->service, 'getAllPaymentMethods']);

        // assert
        $expectedMethods = [
            new PaymentMethod('przelewy24', PaymentMethodNames::PAYMENT_METHOD_NAMES['przelewy24'], true),
            new PaymentMethod('giropay', PaymentMethodNames::PAYMENT_METHOD_NAMES['giropay'], false),
            new PaymentMethod('twint', PaymentMethodNames::PAYMENT_METHOD_NAMES['twint'], true),
            new PaymentMethod('wechatpay', PaymentMethodNames::PAYMENT_METHOD_NAMES['wechatpay'], true),
        ];

        self::assertEquals($expectedMethods, $methods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsConfigAndAPI(): void
    {
        // arrange
        $this->setEntities(
            [
                new PaymentMethodConfig('prepayment', true),
                new PaymentMethodConfig('post-finance-card', false),
                new PaymentMethodConfig('paylater-direct-debit', false),
                new PaymentMethodConfig('bancontact', true),
            ]
        );

        $this->mockData('s-pub-test',
            's-priv-test',
            ['prepayment', 'payu', 'sepa-direct-debit', 'paylater-direct-debit', 'paylater-installment']);

        // act
        $methods = StoreContext::doWithStore('1', [$this->service, 'getAllPaymentMethods']);

        // assert
        $expectedMethods = [
            new PaymentMethod('prepayment', PaymentMethodNames::PAYMENT_METHOD_NAMES['prepayment'], true),
            new PaymentMethod('payu', PaymentMethodNames::PAYMENT_METHOD_NAMES['payu'], false),
            new PaymentMethod('sepa-direct-debit', PaymentMethodNames::PAYMENT_METHOD_NAMES['sepa-direct-debit'],
                false),
            new PaymentMethod('paylater-direct-debit',
                PaymentMethodNames::PAYMENT_METHOD_NAMES['paylater-direct-debit'], false),
            new PaymentMethod('paylater-installment',
                PaymentMethodNames::PAYMENT_METHOD_NAMES['paylater-installment'], false),
        ];

        self::assertEquals($expectedMethods, $methods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testEnablePaymentConfigInvalidType(): void
    {
        // arrange
        $this->expectException(InvalidPaymentTypeException::class);
        $paymentMethodConfig = new PaymentMethodConfig('test', true);

        // act
        StoreContext::doWithStore('1', [$this->service, 'enablePaymentMethodConfig'], [$paymentMethodConfig]);

        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testEnablePaymentConfig(): void
    {
        // arrange
        $paymentMethodConfig = new PaymentMethodConfig(PaymentMethodTypes::KLARNA, true);

        // act
        StoreContext::doWithStore('1', [$this->service, 'enablePaymentMethodConfig'], [$paymentMethodConfig]);

        // assert
        $savedEntity = $this->paymentMethodConfigRepository->selectOne();

        self::assertEquals($paymentMethodConfig, $savedEntity->getPaymentMethodConfig());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentConfigInvalidType(): void
    {
        // arrange
        $this->expectException(InvalidPaymentTypeException::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'getPaymentMethodConfigByType'], ['test']);

        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentConfigNoConfig(): void
    {
        // arrange
        $this->expectException(PaymentConfigNotFoundException::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'getPaymentMethodConfigByType'], ['eps']);

        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentConfig(): void
    {
        // arrange
        $paymentMethodConfig = new PaymentMethodConfig(PaymentMethodTypes::KLARNA, true);
        $this->setEntities([$paymentMethodConfig]);

        // act
        $config = StoreContext::doWithStore('1', [$this->service, 'getPaymentMethodConfigByType'], ['klarna']);

        // assert
        self::assertEquals($paymentMethodConfig, $config);
    }

    /**
     * @param PaymentMethodConfig[] $paymentMethods
     *
     * @return void
     */
    private function setEntities(array $paymentMethods)
    {
        foreach ($paymentMethods as $method) {
            $entity = new PaymentMethodConfigEntity();
            $entity->setPaymentMethodConfig($method);
            $entity->setStoreId('1');
            $entity->setType($method->getType());
            $this->paymentMethodConfigRepository->save($entity);
        }
    }

    /**
     * @return void
     */
    private function mockData(string $publicKey, string $privateKey, array $types = [])
    {
        $keypair = new KeypairMock();
        $keypair->setPublicKey($publicKey);
        $keypair->setAvailablePaymentTypes($types);
        $unzerMock = new UnzerMock($privateKey);
        $unzerMock->setKeypair($keypair);
        UnzerFactoryMock::getInstance()->setMockUnzer($unzerMock);
    }
}
