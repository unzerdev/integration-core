<?php

namespace Unzer\Core\BusinessLogic\Domain\Webhook\Services;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Service\TransactionSynchronizerService;
use Unzer\Core\BusinessLogic\Domain\Webhook\Handlers\WebhookHandlerRegistry;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Constants\WebhookEvents;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Payment;

/**
 * Class WebhookService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Webhook\Services
 */
class WebhookService
{
    /**
     * @var UnzerFactory $unzerFactory
     */
    private UnzerFactory $unzerFactory;

    /**
     * @var TransactionSynchronizerService $transactionSynchronizerService
     */
    private TransactionSynchronizerService $transactionSynchronizerService;

    /**
     * @param UnzerFactory $unzerFactory
     * @param TransactionSynchronizerService $transactionSynchronizerService
     */
    public function __construct(
        UnzerFactory $unzerFactory,
        TransactionSynchronizerService $transactionSynchronizerService
    ) {
        $this->unzerFactory = $unzerFactory;
        $this->transactionSynchronizerService = $transactionSynchronizerService;
    }

    /**
     * @param Webhook $webhook
     *
     * @return void
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidCurrencyCode
     * @throws TransactionHistoryNotFoundException
     * @throws CurrencyMismatchException
     */
    public function handle(Webhook $webhook): void
    {
        $resource = $this->unzerFactory->makeUnzerAPI()->fetchResourceFromEvent($webhook->toPayload());

        if (!($resource instanceof Payment)) {
            $resource = $this->unzerFactory->makeUnzerAPI()->fetchPayment($webhook->getPaymentId());
        }
        $transactionHistory = $this->transactionSynchronizerService->getAndUpdateTransactionHistoryFromUnzerPayment($resource);
        $this->transactionSynchronizerService->handleOrderStatusChange($transactionHistory);

        WebhookHandlerRegistry::getHandler($webhook->getEvent())->handleEvent($webhook, $resource);

        if ($webhook->getEvent() === WebhookEvents::CHARGE_CANCELED) {
            $this->transactionSynchronizerService->handleRefund($transactionHistory);

            return;
        }

        if ($webhook->getEvent() === WebhookEvents::AUTHORIZE_CANCELED) {
            $this->transactionSynchronizerService->handleCancellation($transactionHistory);

            return;
        }

        if ($webhook->getEvent() === WebhookEvents::CHARGE_SUCCEEDED) {
            $this->transactionSynchronizerService->handleCharge($transactionHistory);
        }
    }
}
