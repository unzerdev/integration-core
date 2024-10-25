<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use UnzerSDK\Resources\TransactionTypes\Cancellation;

/**
 * Class CancellationSDK.
 *
 * @package BusinessLogic\Common\Mocks
 */
class CancellationSDK extends Cancellation
{
    /**
     * @param bool $isSuccess
     *
     * @return self
     */
    public function setIsSuccess(bool $isSuccess): self
    {
        return parent::setIsSuccess($isSuccess);
    }
}
