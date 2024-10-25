<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors;

use Unzer\Core\Infrastructure\ServiceRegister;

/**
 * Class Registry
 *
 * @template T of PaymentPageProcessor
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
class PaymentPageProcessorsRegistry
{
    /**
     * Map of global registered processors that will be applied for all payment types
     *
     * @var array<class-string<T>, class-string<T>>
     */
    private static $globalProcessors = [];

    /**
     * Map of payment type specific registered processors
     *
     * @var array<string, array<class-string<T>, class-string<T>>
     */
    private static $typedProcessors = [];

    /**
     * Registers global payment request processor that can be applied for all payment method types
     *
     * @param class-string<T> $processorClass
     * @return void
     */
    public static function registerGlobal(string $processorClass): void
    {
        static::$globalProcessors[$processorClass] = $processorClass;
    }

    /**
     * Registers payment method specific processor that can be applied only for specified payment method type
     *
     * @param string $type
     * @param class-string<T> $processorClass
     * @return void
     */
    public static function registerByPaymentType(string $type, string $processorClass): void
    {
        static::$typedProcessors[$type][$processorClass] = $processorClass;
    }

    /**
     * Gets all applicable payment request processors for a given payment method type
     *
     * @param string $type
     * @return PaymentPageProcessor[] Applicable processors (includes both global and type-specific)
     */
    public static function getProcessors(string $type): array
    {
        return array_map(static function (string $paymentProcessorClass): PaymentPageProcessor {
            return ServiceRegister::getService($paymentProcessorClass);
        }, array_merge(static::$globalProcessors, static::$typedProcessors[$type] ?? []));
    }
}
