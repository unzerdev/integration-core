<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Transaction\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\Transaction\Request\GetTransactionHistoryRequest;
use Unzer\Core\BusinessLogic\AdminAPI\Transaction\Response\GetTransactionHistoryResponse;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;

/**
 * Class TransactionController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Transaction\Controller
 */
class TransactionController
{
    /**
     * @var TransactionHistoryService $transactionHistoryService
     */
    private TransactionHistoryService $transactionHistoryService;

    /**
     * @param TransactionHistoryService $transactionHistoryService
     */
    public function __construct(TransactionHistoryService $transactionHistoryService)
    {
        $this->transactionHistoryService = $transactionHistoryService;
    }

    /**
     * @param GetTransactionHistoryRequest $getTransactionHistoryRequest
     *
     * @return GetTransactionHistoryResponse
     */
    public function getTransactionHistory(
        GetTransactionHistoryRequest $getTransactionHistoryRequest
    ): GetTransactionHistoryResponse {
        return new GetTransactionHistoryResponse(
            $this->transactionHistoryService->getTransactionHistoryByOrderId($getTransactionHistoryRequest->getOrderId())
        );
    }
}
