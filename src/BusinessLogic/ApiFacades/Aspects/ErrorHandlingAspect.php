<?php

namespace Unzer\Core\BusinessLogic\ApiFacades\Aspects;

use Exception;
use Unzer\Core\BusinessLogic\ApiFacades\Response\TranslatableErrorResponse;
use Unzer\Core\BusinessLogic\Bootstrap\Aspect\Aspect;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\BaseTranslatableException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableUnhandledException;
use Throwable;
use Unzer\Core\Infrastructure\Logger\Logger;

/**
 * Class ErrorHandlingAspect.
 *
 * @package Unzer\Core\BusinessLogic\ApiFacades\Aspects
 */
class ErrorHandlingAspect implements Aspect
{
    /**
     * @throws Exception
     */
    public function applyOn(callable $callee, array $params = [])
    {
        try {
            $response = call_user_func_array($callee, $params);
        } catch (BaseTranslatableException $e) {
            Logger::logError(
                $e->getMessage(),
                'Core',
                [
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            $response = TranslatableErrorResponse::fromError($e);
        } catch (Throwable $e) {
            Logger::logError(
                'Unhandled error occurred.',
                'Core',
                [
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            $exception = new BaseTranslatableUnhandledException($e);
            $response = TranslatableErrorResponse::fromError($exception);
        }

        return $response;
    }
}
