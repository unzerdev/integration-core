<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\MetadataProvider;
use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use UnzerSDK\Resources\Metadata;

/**
 * Class MockMetadtaProvider
 *
 * @package BusinessLogic\Common\Mocks
 */
class MockMetadataProvider implements MetadataProvider
{
    public function get(PaymentContext $context): Metadata
    {
        return (new Metadata())
            ->setShopType('test-shop')
            ->setShopVersion('1.0')
            ->addMetadata('pluginType', 'unzerdev/test')
            ->addMetadata('pluginVersion', '1.0.0');
    }
}
