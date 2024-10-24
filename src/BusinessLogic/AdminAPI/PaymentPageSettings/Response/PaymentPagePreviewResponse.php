<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use UnzerSDK\Resources\PaymentTypes\Paypage;

/**
 * Class PaymentPagePreviewResponse
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response
 */
class PaymentPagePreviewResponse extends Response
{
    private Paypage $payPage;

    /**
     * PaymentPageResponse constructor.
     * @param Paypage $payPage
     */
    public function __construct(Paypage $payPage)
    {
        $this->payPage = $payPage;
    }
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'id' => $this->payPage->getId(),
        ];
    }
}
