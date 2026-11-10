<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\Common\Exceptions;

use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\BaseTranslatableException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class PaymentResourceCreationException
 *
 * Unzer refused one of the resources created while starting a payment. The subclass names the step,
 * the original Unzer error is kept as the previous exception.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Payments\Common\Exceptions
 */
abstract class PaymentResourceCreationException extends BaseTranslatableException
{
    /**
     * @param UnzerApiException $unzerException
     */
    public function __construct(UnzerApiException $unzerException)
    {
        parent::__construct(
            new TranslatableLabel(
                static::stepMessage() . ': ' . $unzerException->getMerchantMessage(),
                static::translationCode(),
                [
                    'unzerCode' => (string)$unzerException->getCode(),
                    'merchantMessage' => $unzerException->getMerchantMessage(),
                    'clientMessage' => $unzerException->getClientMessage(),
                ]
            ),
            $unzerException
        );
    }

    /**
     * @return UnzerApiException
     */
    public function getUnzerException(): UnzerApiException
    {
        /** @var UnzerApiException $previous */
        $previous = $this->getPrevious();

        return $previous;
    }

    /**
     * @return string
     */
    abstract protected static function stepMessage(): string;

    /**
     * @return string
     */
    abstract protected static function translationCode(): string;
}
