<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\PaymentPageSettings\Models;

use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions\InvalidUrlException;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\DomainUrls;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class DomainUrlsTest.
 *
 * @package BusinessLogic\Domain\PaymentPageSettings\Models
 */
class DomainUrlsTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testInvalidUrl(): void
    {
        $this->expectException(InvalidUrlException::class);

        new DomainUrls('not-a-valid-url');
    }
}
