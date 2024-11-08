<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\Disconnect\Services;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities\PaymentMethodConfig as PaymentMethodConfigEntity;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities\PaymentPageSettings as PaymentPageSettingsEntity;
use Unzer\Core\BusinessLogic\DataAccess\PaymentStatusMap\Entities\PaymentStatusMap as PaymentStatusMapEntity;
use Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory as TransactionHistoryEntity;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidAmountsException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\UploadedFile;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums\PaymentStatus;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\Translation;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookSettings;
use Unzer\Core\Infrastructure\ORM\Exceptions\EntityClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\Utility\TimeProvider;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookSettings as WebhookDataEntity;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class DisconnectServiceTest.
 *
 * @package Unzer\Core\Tests\BusinessLogic\Domain\Disconnect\Services
 */
class DisconnectServiceTest extends BaseTestCase
{
    /**
     * @var DisconnectService
     */
    public $service;

    /**
     * @var MemoryRepository
     */
    public $webhookDataRepository;

    /**
     * @var MemoryRepository
     */
    public $connectionSettingsRepository;


    /**
     * @var MemoryRepository
     */
    public $paypageSettingsRepository;

    /**
     * @var MemoryRepository
     */
    public $paymentMethodConfigRepository;

    /**
     * @var MemoryRepository
     */
    public $transactionHistoryRepository;

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

        $this->connectionSettingsRepository = TestRepositoryRegistry::getRepository(
            ConnectionSettingsEntity::getClassName()
        );
        $this->webhookDataRepository = TestRepositoryRegistry::getRepository(WebhookDataEntity::getClassName());

        $this->paypageSettingsRepository = TestRepositoryRegistry::getRepository(
            PaymentPageSettingsEntity::getClassName()
        );
        $this->paymentMethodConfigRepository = TestRepositoryRegistry::getRepository(
            PaymentMethodConfigEntity::getClassName()
        );
        $this->transactionHistoryRepository = TestRepositoryRegistry::getRepository(
            TransactionHistoryEntity::getClassName()
        );

        $this->paymentStatusMapRepository = TestRepositoryRegistry::getRepository(
            PaymentStatusMapEntity::getClassName()
        );

        $this->service = TestServiceRegister::getService(DisconnectService::class);
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws Exception
     */
    public function testWebhookDataDeleted(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('s-pub-live-test', 's-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->connectionSettingsRepository->save($settings);

        $oldData = new WebhookDataEntity();
        $webhookSettings = new WebhookSettings(
            Mode::live(),
            new WebhookData('test2.com', ['12', '23'], ['test2', 'test2'], 'test2')
        );
        $oldData->setWebhookSettings($webhookSettings);
        $oldData->setStoreId('1');
        $this->webhookDataRepository->save($oldData);

        // act
        StoreContext::doWithStore('1', [$this->service, 'disconnect']);

        // assert
        /** @var WebhookDataEntity $connectionSettings */
        $webhookDataEntity = $this->webhookDataRepository->select();

        self::assertEmpty($webhookDataEntity);
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws Exception
     */
    public function testConnectionDataDeleted(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('s-pub-live-test', 's-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->connectionSettingsRepository->save($settings);

        $oldData = new WebhookDataEntity();
        $webhookSettings = new WebhookSettings(
            Mode::live(),
            new WebhookData('test2.com', ['12', '23'], ['test2', 'test2'], 'test2')
        );
        $oldData->setWebhookSettings($webhookSettings);
        $oldData->setStoreId('1');
        $this->webhookDataRepository->save($oldData);

        // act
        StoreContext::doWithStore('1', [$this->service, 'disconnect']);

        // assert
        /** @var WebhookDataEntity $connectionSettings */
        $connectionSettings = $this->connectionSettingsRepository->select();

        self::assertEmpty($connectionSettings);
    }

    /**
     * @return void
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException|InvalidAmountsException
     */
    public function testPaymentMethodConfigDataDeleted(): void
    {
        // arrange
        $config = new PaymentMethodConfig('eps', true, BookingMethod::charge());

        $configEntity = new PaymentMethodConfigEntity();
        $configEntity->setPaymentMethodConfig($config);
        $configEntity->setType('eps');
        $configEntity->setStoreId('1');
        $this->paymentMethodConfigRepository->save($configEntity);

        // act
        StoreContext::doWithStore('1', [$this->service, 'disconnect']);

        // assert
        /** @var PaymentMethodConfigEntity $paymentMethodConfigEntity */
        $paymentMethodConfigEntity = $this->paymentMethodConfigRepository->select();

        self::assertEmpty($paymentMethodConfigEntity);
    }

    /**
     * @return void
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws InvalidTranslatableArrayException
     * @throws Exception
     */
    public function testPaymentPageSettingDataDeleted(): void
    {
        // arrange
        $settings = new PaymentPageSettings(
            new UploadedFile('url'),
            TranslationCollection::fromArray([['locale'=>'default','value'=>'shop'], ['locale'=>'en_us','value'=>'shop']]),
            new TranslationCollection(new Translation('en_us',"description")),
        );

        $settingsEntity = new PaymentPageSettingsEntity();
        $settingsEntity->setPaymentPageSetting($settings);
        $settingsEntity->setStoreId('1');
        $this->paypageSettingsRepository->save($settingsEntity);

        // act
        StoreContext::doWithStore('1', [$this->service, 'disconnect']);

        // assert
        /** @var PaymentPageSettingsEntity $paymentPageSettingsEntity */
        $paymentPageSettingsEntity = $this->paypageSettingsRepository->select();

        self::assertEmpty($paymentPageSettingsEntity);
    }

    /**
     * @return void
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws Exception
     */
    public function testPaymentStatusMapDataDeleted(): void
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

        $settingsEntity = new PaymentStatusMapEntity();
        $settingsEntity->setPaymentStatusMap($map);
        $settingsEntity->setStoreId('1');
        $this->paymentStatusMapRepository->save($settingsEntity);

        // act
        StoreContext::doWithStore('1', [$this->service, 'disconnect']);

        // assert
        /** @var PaymentStatusMapEntity $paymentStatusMapEntity */
        $paymentStatusMapEntity = $this->paymentStatusMapRepository->select();

        self::assertEmpty($paymentStatusMapEntity);
    }

    /**
     * @throws QueryFilterInvalidParamException
     * @throws EntityClassException
     */
    public function testTransactionHistoryDataDeleted() : void
    {
        $transactionHistory1 = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );

        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory1);
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
        $this->transactionHistoryRepository->save($configEntity);

        // act
        StoreContext::doWithStore('1', [$this->service, 'disconnect']);

        // assert
        /** @var TransactionHistoryEntity $transactionHistoryEntity */
        $transactionHistoryEntity = $this->transactionHistoryRepository->select();

        self::assertEmpty($transactionHistoryEntity);
    }
}
