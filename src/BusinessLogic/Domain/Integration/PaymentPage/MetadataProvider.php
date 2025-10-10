<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage;

use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use UnzerSDK\Resources\Metadata;

/**
 * Interface MetadataProvider
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage
 */
interface MetadataProvider
{
    public function get(PaymentContext $context): Metadata;
}
