<?php

namespace BusinessLogic\Domain\PaymentPageSettings\Services;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\UploadedFile;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities\PaymentPageSettings as PaymentPageSettingsEntity;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\UploaderServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings as PaymentPageSettingsModel;

/**
 * Class PaymentPageSettingsServiceTest.
 *
 * @package Unzer\Core\Tests\BusinessLogic\Domain\PaymentPageSettings\Services
 */
class PaymentPageSettingsServiceTest extends BaseTestCase
{
    /**
     * @var MemoryRepository
     */
    private $repository;

    /**
     * @var PaymentPageSettingsService
     */
    private PaymentPageSettingsService $service;

    /**
     * @var UploaderServiceMock
     */
    private UploaderServiceMock $uploaderService;

    private UnzerFactory $unzerService;

    /**
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->uploaderService = new UploaderServiceMock();

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

        $this->repository = TestRepositoryRegistry::getRepository(PaymentPageSettingsEntity::getClassName());
        $this->service = TestServiceRegister::getService(PaymentPageSettingsService::class);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsNoSettings(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->service, 'getPaymentPageSettings']);

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
            new UploadedFile('url'),
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")]
        );
        $settingsEntity = new PaymentPageSettingsEntity();
        $settingsEntity->setPaymentPageSetting($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('1', [$this->service, 'getPaymentPageSettings']);

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
            new UploadedFile('url'),
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")]
        );
        $settingsEntity = new PaymentPageSettingsEntity();
        $settingsEntity->setPaymentPageSetting($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('2', [$this->service, 'getPaymentPageSettings']);

        // assert
        self::assertNull($result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSaveSettingsWithoutFile(): void
    {
        // arrange
        $settings = new PaymentPageSettingsModel(
            new UploadedFile('url'),
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")]
        );

        // act
        $result = StoreContext::doWithStore('1', [$this->service, 'savePaymentPageSettings'], [$settings]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($settings, $savedEntity[0]->getPaymentPageSettings());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSaveSettingsWithFile(): void
    {
        // arrange
        $settings = new PaymentPageSettingsModel(
            new UploadedFile(null, new \SplFileInfo('path')),
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")]
        );

        $uploadedPath = 'new path';

        $this->uploaderService->setPath($uploadedPath);

        $newSettings = new PaymentPageSettingsModel(
            new UploadedFile($uploadedPath),
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")]
        );

        // act
        StoreContext::doWithStore('1', [$this->service, 'savePaymentPageSettings'], [$settings]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($newSettings, $savedEntity[0]->getPaymentPageSettings());
    }

    /**
     * @throws Exception
     */
    public function testSaveSettingsAlreadyExists(): void
    {
        // arrange
        $settings = new PaymentPageSettingsModel(
            new UploadedFile(null, new \SplFileInfo('path')),
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
            '#FFFFFFF',
            '#666666',
        );

        $settingsEntity = new PaymentPageSettingsEntity();
        $settingsEntity->setPaymentPageSetting($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $newSettings = new PaymentPageSettingsModel(
            new UploadedFile(null, null),
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
        );

        // act
        StoreContext::doWithStore('1', [$this->service, 'savePaymentPageSettings'], [$newSettings]);

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
            new UploadedFile(null),
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
        );

        $settingsEntity = new PaymentPageSettingsEntity();
        $settingsEntity->setPaymentPageSetting($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        $newSettings = new PaymentPageSettingsModel(
            new UploadedFile(null),
            [new TranslatableLabel("Shop1", "en"), new TranslatableLabel("Shop2", "de")],
            [new TranslatableLabel("Description", "en")],
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
        );

        // act
        StoreContext::doWithStore('2', [$this->service, 'savePaymentPageSettings'], [$newSettings]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(2, $savedEntity);
        self::assertEquals($settings, $savedEntity[0]->getPaymentPageSettings());
        self::assertEquals($newSettings, $savedEntity[1]->getPaymentPageSettings());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCreateMockPaypageNoSettings() : void
    {
        //arrange
        $settings = new PaymentPageSettingsModel(new UploadedFile(null,null),
            [new TranslatableLabel("Shop1", "default")],
            [new TranslatableLabel("Description", "default")]);


        $id = "123";
        $url = "url";
        $this->unzerService->getMockUnzer()->setPayPageData(["id" =>$id, "redirectUrl" => $url]);

        //act
        $paypage = StoreContext::doWithStore('1', [$this->service, 'createMockPaypage'], [$settings]);


        //assert
        self::assertEquals($id, $paypage->getId());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCreateMockPaypageWithSettings() : void
    {
        //arrange
        $settings = new PaymentPageSettingsModel(new UploadedFile("url",null),
            [new TranslatableLabel("Shop1", "default")],
            [new TranslatableLabel("Description", "default")],
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#FFFFFF',
            '#666666',
        );


        $id = "123";
        $url = "url";
        $this->unzerService->getMockUnzer()->setPayPageData(["id" =>$id, "redirectUrl" => $url]);

        //act
        $paypage = StoreContext::doWithStore('1', [$this->service, 'createMockPaypage'], [$settings]);

        //assert
        self::assertEquals($id, $paypage->getId());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCreateMockPaypageWithFile() : void
    {
        //arrange
        $settings = new PaymentPageSettingsModel(new UploadedFile(null,new \SplFileInfo('path')),
            [new TranslatableLabel("Shop1", "default")],
            [new TranslatableLabel("Description", "default")],
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#FFFFFF',
            '#666666',
        );

        $path = 'new path';

        $this->uploaderService->setPath($path);

        $id = "123";
        $url = "url";
        $this->unzerService->getMockUnzer()->setPayPageData(["id" =>$id, "redirectUrl" => $url]);

        //act
        $paypage = StoreContext::doWithStore('1', [$this->service, 'createMockPaypage'], [$settings]);

        //assert
        self::assertEquals($id, $paypage->getId());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCreateMockPaypageWithNoDefaultLabel() : void
    {
        //arrange
        $settings = new PaymentPageSettingsModel(new UploadedFile(null,new \SplFileInfo('path')),
            [new TranslatableLabel("Shop1", "")],
            [new TranslatableLabel("Description", "")],
            '#FFFFFF',
            '#666666',
            '#111111',
            '#555555',
            '#FFFFFF',
            '#666666',
        );

        $path = 'new path';

        $this->uploaderService->setPath($path);

        $id = "123";
        $url = "url";
        $this->unzerService->getMockUnzer()->setPayPageData(["id" =>$id, "redirectUrl" => $url]);

        //act
        $paypage = StoreContext::doWithStore('1', [$this->service, 'createMockPaypage'], [$settings]);

        //assert
        self::assertEquals($id, $paypage->getId());
    }
}