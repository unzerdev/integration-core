<?php

namespace Unzer\Core\BusinessLogic\WebhookAPI\Handler\Controller;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Webhook\Services\WebhookService;
use Unzer\Core\BusinessLogic\WebhookAPI\Validation\Request\WebhookHandleRequest;
use Unzer\Core\BusinessLogic\WebhookAPI\Validation\Response\WebhookHandleResponse;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class WebhookHandlerController.
 *
 * @package Unzer\Core\BusinessLogic\WebhookAPI\Handler\Controller
 */
class WebhookHandlerController
{
    /** @var WebhookService $webhookService */
    private WebhookService $webhookService;

    /**
     * @param WebhookService $webhookService
     */
    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * @param WebhookHandleRequest $request
     *
     * @return WebhookHandleResponse
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     * @throws CurrencyMismatchException
     * @throws InvalidCurrencyCode
     * @throws TransactionHistoryNotFoundException
     */
    public function handle(WebhookHandleRequest $request): WebhookHandleResponse
    {
        $this->webhookService->handle($request->toDomainModel());

        return new WebhookHandleResponse();
    }
}
