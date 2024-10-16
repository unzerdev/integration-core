<?php

namespace BusinessLogic\DataAccess\PaymentPageSettings\Repositories;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities\PaymentPageSettings as PaymentPageSettingsEntity;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings as PaymentPageSettingsModel;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PaymentPageSettingsRepositoryTest.
 *
 * @package BusinessLogic\DataAccess\PaymentPageSettings\Repositories
 */
class PaymentPageSettingsRepositoryTest extends BaseTestCase
{
    /**
     * @var RepositoryInterface
     */
    private RepositoryInterface $repository;

    /**
     * @var PaymentPageSettingsRepositoryInterface
     */
    private PaymentPageSettingsRepositoryInterface $paymentPageSettingsRepository;

    /**
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(PaymentPageSettingsEntity::getClassName());
        $this->paymentPageSettingsRepository = TestServiceRegister::getService(
            PaymentPageSettingsRepositoryInterface::class
        );
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsNoSettings(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->paymentPageSettingsRepository, 'getPaymentPageSettings']);

        // assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettings(): void
    {
        // arrange
        $settings = new PaymentPageSettingsModel(
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
        );
        $settingsEntity = new PaymentPageSettingsEntity();
        $settingsEntity->setPaymentPageSetting($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('1', [$this->paymentPageSettingsRepository, 'getPaymentPageSettings']);

        // assert
        self::assertEquals($settings, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsSetForDifferentStore(): void
    {
        // arrange
        $settings = new PaymentPageSettingsModel(
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")]
        );
        $settingsEntity = new PaymentPageSettingsEntity();
        $settingsEntity->setPaymentPageSetting($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('2', [$this->paymentPageSettingsRepository, 'getPaymentPageSettings']);

        // assert
        self::assertNull($result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetSettings(): void
    {
        // arrange
        $settings = new PaymentPageSettingsModel(
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
        );

        // act
        StoreContext::doWithStore('1', [$this->paymentPageSettingsRepository, 'setPaymentPageSettings'], [$settings]);


        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($settings, $savedEntity[0]->getPaymentPageSettings());
    }

    /**
     * @throws Exception
     */
    public function testSetSettingsAlreadyExists(): void
    {
        // arrange
        $settings = new PaymentPageSettingsModel(
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
        );

        $settingsEntity = new PaymentPageSettingsEntity();
        $settingsEntity->setPaymentPageSetting($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $newSettings = new PaymentPageSettingsModel(
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
        );

        // act
        StoreContext::doWithStore('1', [$this->paymentPageSettingsRepository, 'setPaymentPageSettings'], [$newSettings]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($newSettings, $savedEntity[0]->getPaymentPageSettings());
    }

    /**
     * @throws Exception
     */
    public function testSetSettingsAlreadyExistsForOtherStore(): void
    {
        // arrange
        $settings = new PaymentPageSettingsModel(
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")]
        );

        $settingsEntity = new PaymentPageSettingsEntity();
        $settingsEntity->setPaymentPageSetting($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        $newSettings = new PaymentPageSettingsModel(
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555'
        );

        // act
        StoreContext::doWithStore('2', [$this->paymentPageSettingsRepository, 'setPaymentPageSettings'], [$newSettings]);


        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(2, $savedEntity);
        self::assertEquals($settings, $savedEntity[0]->getPaymentPageSettings());
        self::assertEquals($newSettings, $savedEntity[1]->getPaymentPageSettings());
    }

    /**
     * @throws Exception
     */
    public function testDeleteSettingsExists(): void
    {
        // arrange
        $settings = new PaymentPageSettingsModel(
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")]
        );

        $settingsEntity = new PaymentPageSettingsEntity();
        $settingsEntity->setPaymentPageSetting($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        StoreContext::doWithStore('1', [$this->paymentPageSettingsRepository, 'deletePaymentPageSettings']);


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
        StoreContext::doWithStore('1', [$this->paymentPageSettingsRepository, 'deletePaymentPageSettings']);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(0,$savedEntity);
    }

    /**
     * @throws Exception
     */
    public function testDeleteSettingsOnDifferentStore(): void
    {
        // arrange
        $settings = new PaymentPageSettingsModel(
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")]
        );

        $settingsEntity = new PaymentPageSettingsEntity();
        $settingsEntity->setPaymentPageSetting($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        StoreContext::doWithStore('2', [$this->paymentPageSettingsRepository, 'deletePaymentPageSettings']);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(1,$savedEntity);
    }
}