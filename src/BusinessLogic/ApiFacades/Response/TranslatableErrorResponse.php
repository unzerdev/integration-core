<?php

namespace Unzer\Core\BusinessLogic\ApiFacades\Response;

use Unzer\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;

/**
 * Class TranslatableErrorResponse
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Response
 */
class TranslatableErrorResponse extends ErrorResponse
{
    /**
     * @var BaseTranslatableException
     */
    protected \Throwable $error;

    /**
     * @param BaseTranslatableException $error
     */
    public function __construct(BaseTranslatableException $error)
    {
        parent::__construct($error);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'statusCode' => $this->error->getCode(),
            'errorCode' => $this->error->getTranslatableLabel()->getCode(),
            'errorMessage' => $this->error->getTranslatableLabel()->getMessage(),
            'errorParameters' => $this->error->getTranslatableLabel()->getParams(),
        ];
    }
}
