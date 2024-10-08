<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;

/**
 * Class PaymentPageSettingsPutResponse
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response
 */
class PaymentPageSettingsPutResponse extends Response
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return ['success' => true];
    }

}