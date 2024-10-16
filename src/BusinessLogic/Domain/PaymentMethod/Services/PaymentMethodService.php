<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodNames;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidPaymentTypeException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Unzer;

/**
 * Class PaymentMethodService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services
 */
class PaymentMethodService
{
    /**
     * @var Unzer
     */
    private Unzer $unzer;

    /**
     * @var PaymentMethodConfigRepositoryInterface
     */
    private PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository;

    /**
     * @param Unzer $unzer
     * @param PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository
     */
    public function __construct(Unzer $unzer, PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository)
    {
        $this->unzer = $unzer;
        $this->paymentMethodConfigRepository = $paymentMethodConfigRepository;
    }

    /**
     * Gets all available payment methods for list of payment methods page.
     *
     * @return PaymentMethod[]
     *
     * @throws UnzerApiException
     */
    public function getAllPaymentMethods(): array
    {
        $keypair = $this->unzer->fetchKeypair();
        $availablePaymentTypes = $keypair->getAvailablePaymentTypes();
        $configuredPaymentMethods = $this->paymentMethodConfigRepository->getPaymentMethodConfigs();

        return array_map(function ($availablePaymentType) use ($configuredPaymentMethods) {
            return new PaymentMethod(
                $availablePaymentType,
                PaymentMethodNames::PAYMENT_METHOD_NAMES[$availablePaymentType] ?? PaymentMethodNames::DEFAULT_PAYMENT_METHOD_NAME,
                $this->isPaymentTypeEnabled($availablePaymentType, $configuredPaymentMethods)
            );
        }, $availablePaymentTypes);
    }

    /**
     * @param PaymentMethodConfig $paymentMethodConfig
     *
     * @return void
     *
     * @throws InvalidPaymentTypeException
     */
    public function enablePaymentMethodConfig(PaymentMethodConfig $paymentMethodConfig): void
    {
        if (!in_array($paymentMethodConfig->getType(), PaymentMethodTypes::PAYMENT_TYPES)) {
            throw new InvalidPaymentTypeException(
                new TranslatableLabel(
                    'Payment method type: ' . $paymentMethodConfig->getType() . ' is not supported',
                    'paymentMethod.invalidType'
                ),
            );
        }

        $this->paymentMethodConfigRepository->savePaymentMethodConfig($paymentMethodConfig);
    }

    /**
     * @param string $type
     *
     * @return PaymentMethodConfig
     *
     * @throws InvalidPaymentTypeException
     * @throws PaymentConfigNotFoundException
     */
    public function getPaymentMethodConfigByType(string $type): PaymentMethodConfig
    {
        if (!in_array($type, PaymentMethodTypes::PAYMENT_TYPES)) {
            throw new InvalidPaymentTypeException(
                new TranslatableLabel(
                    'Payment method type: ' . $type . ' is not supported',
                    'paymentMethod.invalidType'
                ),
            );
        }

        $config = $this->paymentMethodConfigRepository->getPaymentMethodConfigByType($type);

        if (!$config) {
            throw new PaymentConfigNotFoundException(
                new TranslatableLabel(
                    'Payment method config for type: ' . $type . ' not found',
                    'paymentMethod.configNotFound'
                ),
            );
        }

        return $config;
    }

    /**
     * @param PaymentMethodConfig $paymentMethodConfig
     *
     * @return void
     */
    public function savePaymentMethodConfig(PaymentMethodConfig $paymentMethodConfig): void
    {
        $this->paymentMethodConfigRepository->savePaymentMethodConfig($paymentMethodConfig);
    }

    /**
     * Returns true if payment method is already saved in database and if it is enabled.
     *
     * @param string $type
     * @param array $paymentMethodConfigs
     *
     * @return bool
     */
    private function isPaymentTypeEnabled(string $type, array $paymentMethodConfigs): bool
    {
        return !empty(array_filter($paymentMethodConfigs, function ($config) use ($type) {
            return $config->getType() === $type && $config->isEnabled();
        }));
    }
}
