<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors;

use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Resources\PaymentTypes\Paypage;

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
     * @param UnzerFactory $unzerFactory
     */
    public function __construct(UnzerFactory $unzerFactory)
    {
        $this->unzerFactory = $unzerFactory;
    }

    public function process(Paypage $payPageRequest, PaymentPageCreateContext $context): void
    {
        $payPageRequest->setExcludeTypes($this->getExcludePaymentTypesList(
            $this->unzerFactory->makeUnzerAPI()->fetchKeypair()->getAvailablePaymentTypes(),
            $context->getPaymentMethodType()
        ));
    }

    private function getExcludePaymentTypesList(array $availablePaymentTypes, string $selectedPaymentType): array
    {
        return array_values(array_filter(
            array_unique($availablePaymentTypes),
            static function ($paymentMethodType) use ($selectedPaymentType) {
                return $paymentMethodType !== $selectedPaymentType;
            }
        ));
    }
}
