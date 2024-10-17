<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\EnablePaymentMethodRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\GetPaymentMethodConfigRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\SavePaymentMethodConfigRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response\EnablePaymentMethodResponse;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response\GetPaymentConfigResponse;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response\PaymentMethodsResponse;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response\SavePaymentMethodConfigResponse;
use Unzer\Core\BusinessLogic\Domain\Country\Exceptions\InvalidCountryArrayException;
use Unzer\Core\BusinessLogic\Domain\Integration\Currency\CurrencyServiceInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidBookingMethodException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidPaymentTypeException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class PaymentMethodsController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Controller
 */
class PaymentMethodsController
{
    /**
     * @var PaymentMethodService
     */
    private PaymentMethodService $paymentMethodService;

    /**
     * @var CurrencyServiceInterface
     */
    private CurrencyServiceInterface $currencyService;

    /**
     * @param PaymentMethodService $paymentMethodService
     * @param CurrencyServiceInterface $currencyService
     */
    public function __construct(PaymentMethodService $paymentMethodService, CurrencyServiceInterface $currencyService)
    {
        $this->paymentMethodService = $paymentMethodService;
        $this->currencyService = $currencyService;
    }

    /**
     * @return PaymentMethodsResponse
     *
     * @throws UnzerApiException
     */
    public function getPaymentMethods(): PaymentMethodsResponse
    {
        return new PaymentMethodsResponse($this->paymentMethodService->getAllPaymentMethods());
    }

    /**
     * @param EnablePaymentMethodRequest $request
     *
     * @return EnablePaymentMethodResponse
     *
     * @throws InvalidPaymentTypeException
     */
    public function enablePaymentMethod(EnablePaymentMethodRequest $request): EnablePaymentMethodResponse
    {
        $this->paymentMethodService->enablePaymentMethodConfig($request->toDomainModel());

        return new EnablePaymentMethodResponse();
    }

    /**
     * @param GetPaymentMethodConfigRequest $request
     *
     * @return GetPaymentConfigResponse
     *
     * @throws InvalidPaymentTypeException
     * @throws PaymentConfigNotFoundException
     */
    public function getPaymentConfig(GetPaymentMethodConfigRequest $request): GetPaymentConfigResponse
    {
        return new GetPaymentConfigResponse($this->paymentMethodService->getPaymentMethodConfigByType($request->getType()));
    }

    /**
     * @param SavePaymentMethodConfigRequest $request
     *
     * @return SavePaymentMethodConfigResponse
     *
     * @throws InvalidTranslatableArrayException
     * @throws InvalidCountryArrayException
     * @throws InvalidBookingMethodException
     */
    public function savePaymentConfig(SavePaymentMethodConfigRequest $request): SavePaymentMethodConfigResponse
    {
        $this->paymentMethodService->savePaymentMethodConfig($request->toDomainModel($this->currencyService->getDefaultCurrency()));

        return new SavePaymentMethodConfigResponse();
    }
}
