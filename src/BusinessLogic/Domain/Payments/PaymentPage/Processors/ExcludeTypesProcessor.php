<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Processors;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\ExcludedTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig as PaymentMethodConfigModel;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\EmbeddedResources\Paypage\PaymentMethodConfig;
use UnzerSDK\Resources\EmbeddedResources\Paypage\PaymentMethodsConfigs;
use UnzerSDK\Resources\V2\Paypage;

/**
 * Class RequiredDataProcessor
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
class ExcludeTypesProcessor implements PaymentPageProcessor
{
    private UnzerFactory $unzerFactory;

    /**
     * excludeTypesProcessor constructor.
     *
     * @param UnzerFactory $unzerFactory
     */
    public function __construct(UnzerFactory $unzerFactory)
    {
        $this->unzerFactory = $unzerFactory;
    }

    /**
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    public function process(
        Paypage $payPageRequest,
        PaymentPageCreateContext $context,
        PaymentMethodConfigModel $paymentMethodConfiguration
    ): void {
        $paymentMethodConfig = $this->setExcludedMethodConfigs(
            $this->getExcludePaymentTypesList(
                $this->unzerFactory->makeUnzerAPI()->fetchKeypair()->getAvailablePaymentTypes(),
                $context->getPaymentMethodType()
            ),
            $paymentMethodConfiguration
        );

        $payPageRequest->setPaymentMethodsConfigs($paymentMethodConfig);
    }

    /**
     * @param array $availablePaymentTypes
     * @param string $selectedPaymentType
     *
     * @return array
     */
    protected function getExcludePaymentTypesList(array $availablePaymentTypes, string $selectedPaymentType): array
    {
        return array_values(
            array_filter(
                array_unique($availablePaymentTypes),
                static function ($paymentMethodType) use ($selectedPaymentType) {
                    return $paymentMethodType !== $selectedPaymentType;
                }
            )
        );
    }

    /**
     * @param array $excludedTypes
     * @param PaymentMethodConfigModel $methodConfig
     *
     * @return PaymentMethodsConfigs
     */
    protected function setExcludedMethodConfigs(
        array $excludedTypes,
        PaymentMethodConfigModel $methodConfig
    ): PaymentMethodsConfigs {
        $paymentMethodConfigs = new PaymentMethodsConfigs();
        foreach ($excludedTypes as $method) {
            if ($method === PaymentMethodTypes::CLICK_TO_PAY && $methodConfig->getType() === PaymentMethodTypes::CARDS) {
                !$methodConfig->isClickToPayEnabled() && $paymentMethodConfigs->addMethodConfig(
                    PaymentMethodTypes::CLICK_TO_PAY,
                    new PaymentMethodConfig(false)
                );

                continue;
            }
            if (isset(ExcludedTypes::EXCLUDED_METHOD_NAMES[$method])) {
                $paymentMethodConfigs->addMethodConfig(
                    ExcludedTypes::EXCLUDED_METHOD_NAMES[$method],
                    new PaymentMethodConfig(false)
                );
            }
        }

        return $paymentMethodConfigs;
    }
}
