<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage;

use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\Metadata;

/**
 * Interface MetadataProvider
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage
 */
interface MetadataProvider
{
    public function get(PaymentPageCreateContext $context): Metadata;
}
