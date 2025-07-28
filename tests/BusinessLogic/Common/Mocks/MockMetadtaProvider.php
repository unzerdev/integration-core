<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\MetadataProvider;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\Metadata;

/**
 * Class MockMetadtaProvider
 *
 * @package BusinessLogic\Common\Mocks
 */
class MockMetadtaProvider implements MetaDataProvider
{
    public function get(PaymentPageCreateContext $context): Metadata
    {
        return (new Metadata())
            ->setShopType('test-shop')
            ->setShopVersion('1.0')
            ->addMetadata('pluginType', 'unzerdev/test')
            ->addMetadata('pluginVersion', '1.0.0');
    }
}
